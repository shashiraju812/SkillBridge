package servlet;

import java.io.IOException;
import java.io.PrintWriter;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

/**
 * Practical 12: Servlet Authentication
 * Validates credentials against MySQL and initiates an authenticated session.
 */
@WebServlet("/login-servlet")
public class LoginServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    private static final String DB_URL = "jdbc:mysql://localhost:3306/skillbridge?useSSL=false&serverTimezone=UTC";
    private static final String DB_USER = "root";
    private static final String DB_PASS = "";

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        response.setContentType("text/html;charset=UTF-8");
        PrintWriter out = response.getWriter();

        String email = request.getParameter("email");
        String password = request.getParameter("password");

        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            try (Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASS)) {
                String sql = "SELECT id, full_name, role, is_active FROM users WHERE email = ?";
                try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                    stmt.setString(1, email);
                    try (ResultSet rs = stmt.executeQuery()) {
                        if (rs.next()) {
                            // User found
                            String name = rs.getString("full_name");
                            String role = rs.getString("role");
                            boolean active = rs.getBoolean("is_active");

                            if (!active) {
                                out.println("<h3 style='color:red;'>Account deactivated.</h3>");
                                return;
                            }

                            // Create authenticated session
                            HttpSession session = request.getSession(true);
                            session.setAttribute("user_email", email);
                            session.setAttribute("user_name", name);
                            session.setAttribute("user_role", role);

                            out.println("<!DOCTYPE html><html><head><title>Login Success</title></head><body>");
                            out.println("<h2 style='color:#0d9488;'>Authentication Succeeded!</h2>");
                            out.println("<p>Welcome, <strong>" + name + "</strong> (" + role + ")</p>");
                            out.println("<p>Session ID: " + session.getId() + "</p>");
                            out.println("<p><a href='session-demo'>Test Session Techniques (Practical 13)</a></p>");
                            out.println("</body></html>");
                        } else {
                            out.println("<h3 style='color:red;'>Invalid credentials. User not found.</h3>");
                            out.println("<a href='javascript:history.back()'>Try Again</a>");
                        }
                    }
                }
            }
        } catch (Exception e) {
            out.println("<h3 style='color:red;'>Authentication Error: " + e.getMessage() + "</h3>");
        } finally {
            out.close();
        }
    }
}


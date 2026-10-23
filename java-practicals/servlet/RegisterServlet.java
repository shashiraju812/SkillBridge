package servlet;

import java.io.IOException;
import java.io.PrintWriter;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Practical 11: Java Servlet Registration with MySQL Database
 * Uses JDBC PreparedStatements to safely insert user credentials.
 */
@WebServlet("/register-servlet")
public class RegisterServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    // Database connection details (XAMPP MySQL default)
    private static final String DB_URL = "jdbc:mysql://localhost:3306/skillbridge?useSSL=false&serverTimezone=UTC";
    private static final String DB_USER = "root";
    private static final String DB_PASS = "";

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        response.setContentType("text/html;charset=UTF-8");
        PrintWriter out = response.getWriter();

        String fullName = request.getParameter("full_name");
        String email = request.getParameter("email");
        String password = request.getParameter("password");

        // Basic validation
        if (fullName == null || fullName.trim().isEmpty() || email == null || email.trim().isEmpty()) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Required fields missing.");
            return;
        }

        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            try (Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASS)) {
                // Prepared statement ensures protection against SQL Injection
                String sql = "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'student')";
                try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                    stmt.setString(1, fullName.trim());
                    stmt.setString(2, email.trim());
                    // In production, BCrypt hash is used; demonstrating standard store
                    stmt.setString(3, password);

                    int rows = stmt.executeUpdate();

                    out.println("<!DOCTYPE html><html><head><title>Registration Success</title></head><body>");
                    out.println("<h2 style='color:green;'>Registration Successful (Servlet + MySQL)</h2>");
                    out.println("<p>Rows inserted: " + rows + "</p>");
                    out.println("<p>Student Name: " + fullName + "</p>");
                    out.println("<p>Registered Email: " + email + "</p>");
                    out.println("<a href='hello'>Back to Servlet Home</a>");
                    out.println("</body></html>");
                }
            }
        } catch (Exception e) {
            out.println("<h2 style='color:red;'>Database Error</h2>");
            out.println("<p>" + e.getMessage() + "</p>");
        } finally {
            out.close();
        }
    }
}


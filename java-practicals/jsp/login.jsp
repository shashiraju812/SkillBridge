<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ page import="java.sql.*" %>

<!DOCTYPE html>
<html>
<head>
    <title>Practical 15: JSP Authentication</title>
    <style>
        body { font-family: sans-serif; background: #f3f4f6; padding: 40px; }
        .card { background: white; max-width: 440px; margin: 0 auto; padding: 24px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 500; }
        input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #0d9488; color: white; padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .error { background: #fee2e2; color: #991b1b; padding: 10px; margin-bottom: 16px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="card">
    <h2 style="color:#0d9488;text-align:center;">JSP Authentication</h2>
    <p style="text-align:center;color:#6b7280;font-size:0.9em;">Practical 15 Demonstration</p>

    <%
    String error = null;
    if ("POST".equalsIgnoreCase(request.getMethod())) {
        String email = request.getParameter("email");
        String password = request.getParameter("password");

        String dbUrl = "jdbc:mysql://localhost:3306/skillbridge?useSSL=false&serverTimezone=UTC";
        String dbUser = "root";
        String dbPass = "";

        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            try (Connection conn = DriverManager.getConnection(dbUrl, dbUser, dbPass)) {
                String sql = "SELECT full_name, role FROM users WHERE email = ?";
                try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                    stmt.setString(1, email);
                    try (ResultSet rs = stmt.executeQuery()) {
                        if (rs.next()) {
                            // Valid user -> set session and redirect
                            session.setAttribute("auth_user", rs.getString("full_name"));
                            session.setAttribute("auth_role", rs.getString("role"));
                            response.sendRedirect("welcome.jsp");
                            return;
                        } else {
                            error = "Invalid credentials. User not found.";
                        }
                    }
                }
            }
        } catch (Exception e) {
            error = "Database Error: " + e.getMessage();
        }
    }
    %>

    <% if (error != null) { %>
        <div class="error"><%= error %></div>
    <% } %>

    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="aarav@student.local">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Log In</button>
    </form>
</div>

</body>
</html>


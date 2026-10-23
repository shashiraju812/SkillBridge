<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<jsp:useBean id="user" class="beans.UserBean" scope="request" />
<jsp:setProperty name="user" property="*" />

<!DOCTYPE html>
<html>
<head>
    <title>Practical 14: JSP Registration with JavaBean</title>
    <style>
        body { font-family: sans-serif; background: #f3f4f6; padding: 40px; }
        .card { background: white; max-width: 480px; margin: 0 auto; padding: 24px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 500; }
        input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #0d9488; color: white; padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .msg { padding: 12px; margin-bottom: 16px; border-radius: 4px; }
        .success { background: #d1fae5; color: #065f46; }
        .error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<div class="card">
    <h2 style="color:#0d9488;text-align:center;">JSP Registration (JavaBean + DB)</h2>
    <p style="text-align:center;color:#6b7280;font-size:0.9em;">Practical 14 Demonstration</p>

    <% 
    if ("POST".equalsIgnoreCase(request.getMethod())) {
        if (user.getFullName() != null && !user.getFullName().trim().isEmpty() &&
            user.getEmail() != null && !user.getEmail().trim().isEmpty()) {
            
            boolean saved = user.saveToDatabase();
            if (saved) {
    %>
                <div class="msg success">
                    <strong>Success!</strong> User <em><jsp:getProperty name="user" property="fullName" /></em> registered via JavaBean.
                </div>
    <%
            } else {
    %>
                <div class="msg error">Registration failed (Database error or duplicate email).</div>
    <%
            }
        } else {
    %>
            <div class="msg error">Please enter all required fields.</div>
    <%
        }
    }
    %>

    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullName" required placeholder="Aarav Sharma">
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="aarav@student.local">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Register via JavaBean</button>
    </form>
</div>

</body>
</html>


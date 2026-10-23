<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%
    String user = (String) session.getAttribute("auth_user");
    String role = (String) session.getAttribute("auth_role");

    if (user == null) {
        response.sendRedirect("login.jsp");
        return;
    }

    if ("logout".equals(request.getParameter("action"))) {
        session.invalidate();
        response.sendRedirect("login.jsp");
        return;
    }
%>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome — JSP Session Protected Page</title>
    <style>
        body { font-family: sans-serif; background: #f3f4f6; padding: 40px; }
        .card { background: white; max-width: 500px; margin: 0 auto; padding: 24px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn { display: inline-block; background: #ef4444; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>

<div class="card">
    <h2 style="color:#0d9488;">Authentication Successful</h2>
    <p>Welcome to the protected JSP dashboard, <strong><%= user %></strong>!</p>
    <p>Role: <span style="background:#e0e7ff;color:#4338ca;padding:3px 8px;border-radius:12px;font-size:0.85em;"><%= role %></span></p>
    <p>Session ID: <code><%= session.getId() %></code></p>
    <p>Session Creation Time: <%= new java.util.Date(session.getCreationTime()) %></p>
    <hr style="margin:20px 0;">
    <a href="welcome.jsp?action=logout" class="btn">Logout from JSP Session</a>
</div>

</body>
</html>


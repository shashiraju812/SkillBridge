package servlet;

import java.io.IOException;
import java.io.PrintWriter;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 * Practical 10: Apache Tomcat & Hello World Servlet
 * Aim: Demonstrate basic Java Servlet structure and lifecycle response.
 */
@WebServlet("/hello")
public class HelloServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        response.setContentType("text/html;charset=UTF-8");
        PrintWriter out = response.getWriter();

        try {
            out.println("<!DOCTYPE html>");
            out.println("<html><head><title>SkillBridge — Hello World Servlet</title>");
            out.println("<style>body{font-family:sans-serif;margin:40px;line-height:1.6;} .card{background:#f9fafb;border:1px solid #e5e7eb;padding:24px;border-radius:8px;max-width:500px;}</style>");
            out.println("</head><body>");
            out.println("<div class='card'>");
            out.println("<h2 style='color:#0d9488;'>Practical 10: Hello World Servlet</h2>");
            out.println("<p>Welcome to <strong>SkillBridge</strong> Web Technologies Lab Practical!</p>");
            out.println("<p>Server Time: " + new java.util.Date() + "</p>");
            out.println("<p>Client IP: " + request.getRemoteAddr() + "</p>");
            out.println("<hr><p style='font-size:0.85em;color:#6b7280;'>Servlet successfully executed via Apache Tomcat container.</p>");
            out.println("</div>");
            out.println("</body></html>");
        } finally {
            out.close();
        }
    }
}


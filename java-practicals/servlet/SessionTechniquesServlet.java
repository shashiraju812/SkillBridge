package servlet;

import java.io.IOException;
import java.io.PrintWriter;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

/**
 * Practical 13: Servlet Session Management Techniques
 * Demonstrates:
 *  1. Cookies (client-side key-value pairs)
 *  2. Hidden form fields (embedded in HTML forms)
 *  3. HttpSession (server-side session memory)
 *  4. URL Rewriting (appending session identifier to URLs)
 */
@WebServlet("/session-demo")
public class SessionTechniquesServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        response.setContentType("text/html;charset=UTF-8");
        PrintWriter out = response.getWriter();

        // 1. Technique 1: Cookies
        Cookie visitCookie = new Cookie("last_visit_time", String.valueOf(System.currentTimeMillis()));
        visitCookie.setMaxAge(3600); // 1 hour
        response.addCookie(visitCookie);

        // Read existing cookies
        String existingCookieVal = "None detected";
        Cookie[] cookies = request.getCookies();
        if (cookies != null) {
            for (Cookie c : cookies) {
                if ("last_visit_time".equals(c.getName())) {
                    existingCookieVal = c.getValue();
                }
            }
        }

        // 2. Technique 2: HttpSession
        HttpSession session = request.getSession(true);
        Integer visitCount = (Integer) session.getAttribute("visit_count");
        if (visitCount == null) {
            visitCount = 1;
        } else {
            visitCount += 1;
        }
        session.setAttribute("visit_count", visitCount);

        // 3. Technique 3: URL Rewriting
        String rewrittenUrl = response.encodeURL("session-demo");

        out.println("<!DOCTYPE html><html><head><title>Practical 13: Session Techniques</title>");
        out.println("<style>body{font-family:sans-serif;margin:40px;line-height:1.6;} .box{border:1px solid #ccc;padding:15px;margin-bottom:15px;border-radius:6px;}</style>");
        out.println("</head><body>");
        out.println("<h2 style='color:#0d9488;'>Practical 13: Session Tracking Techniques</h2>");

        // Section 1: HttpSession
        out.println("<div class='box'>");
        out.println("<h4>1. HttpSession (Server-side Object)</h4>");
        out.println("<p>Session ID: <code>" + session.getId() + "</code></p>");
        out.println("<p>Visit Count in this session: <strong>" + visitCount + "</strong></p>");
        out.println("</div>");

        // Section 2: Cookies
        out.println("<div class='box'>");
        out.println("<h4>2. Cookies (Client-side Storage)</h4>");
        out.println("<p>Stored 'last_visit_time' Cookie Value: <code>" + existingCookieVal + "</code></p>");
        out.println("</div>");

        // Section 3: Hidden Form Fields
        out.println("<div class='box'>");
        out.println("<h4>3. Hidden Form Fields</h4>");
        out.println("<form action='session-demo' method='POST'>");
        out.println("<input type='hidden' name='hidden_tracker_token' value='SKILLBRIDGE-TOKEN-" + session.getId() + "'>");
        out.println("<input type='submit' value='Submit form with hidden token'>");
        out.println("</form>");
        out.println("</div>");

        // Section 4: URL Rewriting
        out.println("<div class='box'>");
        out.println("<h4>4. URL Rewriting (for cookie-disabled clients)</h4>");
        out.println("<p>Encoded URL: <a href='" + rewrittenUrl + "'>" + rewrittenUrl + "</a></p>");
        out.println("</div>");

        out.println("</body></html>");
        out.close();
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) 
            throws ServletException, IOException {
        String token = request.getParameter("hidden_tracker_token");
        response.setContentType("text/html");
        PrintWriter out = response.getWriter();
        out.println("<h3>Hidden Field Received:</h3><p>" + token + "</p>");
        out.println("<a href='session-demo'>Return to Session Demo</a>");
        out.close();
    }
}


<?php
$pageTitle = 'Web Technologies Lab Practicals';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

include __DIR__ . '/../includes/header.php';
?>
<div class="main-content">
<div class="container" style="max-width:1050px;">
    <div class="page-header text-center">
        <h1>Web Technologies Lab (CS351) Practicals</h1>
        <p>Comprehensive interactive modules &amp; source documentation for B.Tech laboratory evaluation</p>
    </div>

    <!-- Practical 4 & 5 Interactive Playground -->
    <div class="card mb-4" style="border: 2px solid var(--primary);">
        <h3 style="color:var(--primary);">Interactive Lab Playgrounds (Practicals 4 &amp; 5)</h3>
        <div class="card-grid-2 mt-2">
            <!-- Practical 4: Number to words -->
            <div style="background:#f9fafb;padding:15px;border-radius:8px;">
                <h4>Practical 4: Number-to-Words (0 - 999)</h4>
                <p class="text-xs text-muted">Client-side JavaScript integer to word converter.</p>
                <div class="form-group">
                    <input type="number" id="p4_input" class="form-control" placeholder="Enter integer 0 - 999" min="0" max="999" oninput="runP4()">
                </div>
                <div style="background:#fff;padding:10px;border:1px solid #e5e7eb;border-radius:6px;min-height:38px;">
                    <strong id="p4_output" class="text-primary">Type a number above...</strong>
                </div>
            </div>

            <!-- Practical 5: Regex validation -->
            <div style="background:#f9fafb;padding:15px;border-radius:8px;">
                <h4>Practical 5: Regular Expressions</h4>
                <p class="text-xs text-muted">Real-time regex format validator with error feedback.</p>
                <div class="d-flex gap-1 mb-1">
                    <select id="p5_type" class="form-control" style="width:130px;" onchange="runP5()">
                        <option value="email">Email</option>
                        <option value="phone">Indian Phone</option>
                        <option value="name">Name</option>
                        <option value="year">Year</option>
                        <option value="url">URL</option>
                    </select>
                    <input type="text" id="p5_input" class="form-control" placeholder="Enter test value" oninput="runP5()">
                </div>
                <div style="background:#fff;padding:10px;border:1px solid #e5e7eb;border-radius:6px;min-height:38px;">
                    <span id="p5_output" class="text-muted">Enter string to validate against regex pattern...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Practicals Master Index Table -->
    <div class="card">
        <h3>Prescribed Practicals Checklist</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Practical Aim</th>
                        <th>Technology</th>
                        <th>Files &amp; Modules</th>
                        <th>Run / Demo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>1</strong></td>
                        <td>HTML Registration Form with controls</td>
                        <td>HTML5</td>
                        <td><code>practicals/practical1.html</code></td>
                        <td><a href="practical1.html" class="btn btn-sm btn-outline" target="_blank">Open Form</a></td>
                    </tr>
                    <tr>
                        <td><strong>2</strong></td>
                        <td>HTML Frameset and Navigation Layout</td>
                        <td>HTML (Legacy Frames)</td>
                        <td><code>practicals/practical2_frameset.html</code></td>
                        <td><a href="practical2_frameset.html" class="btn btn-sm btn-outline" target="_blank">Open Frameset</a></td>
                    </tr>
                    <tr>
                        <td><strong>3</strong></td>
                        <td>CSS &amp; Selection Controls</td>
                        <td>CSS3</td>
                        <td><code>practicals/practical3.html</code></td>
                        <td><a href="practical3.html" class="btn btn-sm btn-outline" target="_blank">View Controls</a></td>
                    </tr>
                    <tr>
                        <td><strong>4</strong></td>
                        <td>JavaScript Number to Words (0 - 999)</td>
                        <td>Vanilla JS</td>
                        <td><code>assets/js/app.js (numberToWords)</code></td>
                        <td><span class="badge badge-success">Live Above</span></td>
                    </tr>
                    <tr>
                        <td><strong>5</strong></td>
                        <td>JavaScript Regular Expressions</td>
                        <td>Vanilla JS (Regex)</td>
                        <td><code>assets/js/app.js (validateField)</code></td>
                        <td><span class="badge badge-success">Live Above</span></td>
                    </tr>
                    <tr>
                        <td><strong>6</strong></td>
                        <td>XML, DTD &amp; XSD Validation</td>
                        <td>XML, DTD, XSD</td>
                        <td><code>xml/student-profiles.xml</code></td>
                        <td><a href="../xml/student-profiles.xml" class="btn btn-sm btn-outline" target="_blank">View XML</a></td>
                    </tr>
                    <tr>
                        <td><strong>7</strong></td>
                        <td>Java DOM &amp; SAX Parsers for 10 Students</td>
                        <td>Java JAXP</td>
                        <td><code>java-practicals/dom/</code> &amp; <code>sax/</code></td>
                        <td><span class="badge badge-primary">Standalone Java</span></td>
                    </tr>
                    <tr>
                        <td><strong>8</strong></td>
                        <td>PHP Registration connected to MySQL</td>
                        <td>PHP + MySQL</td>
                        <td><code>register.php</code></td>
                        <td><a href="../register.php" class="btn btn-sm btn-outline" target="_blank">Open Register</a></td>
                    </tr>
                    <tr>
                        <td><strong>9</strong></td>
                        <td>PHP Authentication &amp; Sessions</td>
                        <td>PHP Sessions</td>
                        <td><code>login.php</code>, <code>includes/auth.php</code></td>
                        <td><a href="../login.php" class="btn btn-sm btn-outline" target="_blank">Open Login</a></td>
                    </tr>
                    <tr>
                        <td><strong>10</strong></td>
                        <td>Tomcat &amp; Hello World Servlet</td>
                        <td>Java Servlet</td>
                        <td><code>java-practicals/servlet/HelloServlet.java</code></td>
                        <td><span class="badge badge-info">Tomcat Servlet</span></td>
                    </tr>
                    <tr>
                        <td><strong>11</strong></td>
                        <td>Servlet Registration with MySQL</td>
                        <td>Java Servlet + JDBC</td>
                        <td><code>java-practicals/servlet/RegisterServlet.java</code></td>
                        <td><span class="badge badge-info">Tomcat Servlet</span></td>
                    </tr>
                    <tr>
                        <td><strong>12</strong></td>
                        <td>Servlet Authentication &amp; Sessions</td>
                        <td>Java Servlet</td>
                        <td><code>java-practicals/servlet/LoginServlet.java</code></td>
                        <td><span class="badge badge-info">Tomcat Servlet</span></td>
                    </tr>
                    <tr>
                        <td><strong>13</strong></td>
                        <td>Servlet Session Techniques (Cookies, Hidden, HttpSession, URL Rewriting)</td>
                        <td>Java Servlet</td>
                        <td><code>java-practicals/servlet/SessionTechniquesServlet.java</code></td>
                        <td><span class="badge badge-info">Tomcat Servlet</span></td>
                    </tr>
                    <tr>
                        <td><strong>14</strong></td>
                        <td>JSP Registration with JavaBeans &amp; MySQL</td>
                        <td>JSP + JavaBean</td>
                        <td><code>java-practicals/jsp/register.jsp</code></td>
                        <td><span class="badge badge-purple">Tomcat JSP</span></td>
                    </tr>
                    <tr>
                        <td><strong>15</strong></td>
                        <td>JSP Authentication &amp; Protected Pages</td>
                        <td>JSP</td>
                        <td><code>java-practicals/jsp/login.jsp</code></td>
                        <td><span class="badge badge-purple">Tomcat JSP</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<script>
function runP4() {
    var val = parseInt(document.getElementById('p4_input').value);
    var out = document.getElementById('p4_output');
    if (isNaN(val)) {
        out.textContent = "Type a number above...";
        out.className = "text-muted";
    } else {
        out.textContent = numberToWords(val);
        out.className = "text-primary";
    }
}

function runP5() {
    var type = document.getElementById('p5_type').value;
    var val = document.getElementById('p5_input').value;
    var out = document.getElementById('p5_output');
    if (!val) {
        out.textContent = "Enter string to validate against regex pattern...";
        out.className = "text-muted";
        return;
    }
    var res = validateField(val, type);
    if (res.valid) {
        out.innerHTML = "✅ <strong>Valid</strong> " + type.toUpperCase();
        out.className = "text-success";
    } else {
        out.innerHTML = "❌ <strong>Invalid:</strong> " + res.message;
        out.className = "text-danger";
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>


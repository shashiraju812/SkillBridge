package beans;

import java.io.Serializable;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;

/**
 * Practical 14: JavaBean for User Registration
 * Encapsulates user profile properties and database persistence method.
 */
public class UserBean implements Serializable {
    private static final long serialVersionUID = 1L;

    private String fullName;
    private String email;
    private String password;
    private String role = "student";

    public UserBean() {
    }

    public String getFullName() {
        return fullName;
    }

    public void setFullName(String fullName) {
        this.fullName = fullName;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public String getRole() {
        return role;
    }

    public void setRole(String role) {
        this.role = role;
    }

    /**
     * Persist user to MySQL database
     */
    public boolean saveToDatabase() {
        String dbUrl = "jdbc:mysql://localhost:3306/skillbridge?useSSL=false&serverTimezone=UTC";
        String dbUser = "root";
        String dbPass = "";

        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            try (Connection conn = DriverManager.getConnection(dbUrl, dbUser, dbPass)) {
                String sql = "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)";
                try (PreparedStatement stmt = conn.prepareStatement(sql)) {
                    stmt.setString(1, this.fullName);
                    stmt.setString(2, this.email);
                    stmt.setString(3, this.password);
                    stmt.setString(4, this.role);
                    return stmt.executeUpdate() > 0;
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
            return false;
        }
    }
}


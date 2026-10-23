package dom;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import org.w3c.dom.*;
import java.io.File;
import java.util.Scanner;

/**
 * Practical 7A: Java DOM Parser for SkillBridge
 * Demonstrates loading an XML tree into memory and searching a student by ID.
 */
public class DOMParserDemo {

    public static void main(String[] args) {
        String xmlFilePath = "xml/student-profiles.xml";
        // Also support absolute or current directory execution
        File xmlFile = new File(xmlFilePath);
        if (!xmlFile.exists()) {
            xmlFile = new File("../../xml/student-profiles.xml");
        }

        try {
            DocumentBuilderFactory factory = DocumentBuilderFactory.newInstance();
            DocumentBuilder builder = factory.newDocumentBuilder();
            Document document = builder.parse(xmlFile);
            document.getDocumentElement().normalize();

            System.out.println("==================================================");
            System.out.println("SkillBridge XML DOM Parser — Practical 7");
            System.out.println("Root Element: " + document.getDocumentElement().getNodeName());
            System.out.println("==================================================");

            String searchId = "101";
            if (args.length > 0) {
                searchId = args[0].replaceAll("[^a-zA-Z0-9]", "").trim();
            } else {
                Scanner scanner = new Scanner(System.in);
                System.out.print("Enter Student ID to search (e.g., 101, 102): ");
                if (scanner.hasNextLine()) {
                    searchId = scanner.nextLine().replaceAll("[^a-zA-Z0-9]", "").trim();
                }
            }

            NodeList studentList = document.getElementsByTagName("student");
            boolean found = false;

            for (int i = 0; i < studentList.getLength(); i++) {
                Node node = studentList.item(i);
                if (node.getNodeType() == Node.ELEMENT_NODE) {
                    Element student = (Element) node;
                    String id = student.getAttribute("id");

                    if (id.equalsIgnoreCase(searchId)) {
                        found = true;
                        System.out.println("\n[Student Found]");
                        System.out.println("ID:              " + id);
                        System.out.println("Name:            " + getTagValue("name", student));
                        System.out.println("Email:           " + getTagValue("email", student));
                        System.out.println("College:         " + getTagValue("college", student));
                        System.out.println("Degree:          " + getTagValue("degree", student) + " (" + getTagValue("branch", student) + ")");
                        System.out.println("Graduation Year: " + getTagValue("graduation_year", student));

                        System.out.println("Skills:");
                        NodeList skills = student.getElementsByTagName("skill");
                        for (int s = 0; s < skills.getLength(); s++) {
                            Element skillElem = (Element) skills.item(s);
                            String prof = skillElem.getAttribute("proficiency");
                            System.out.println("  • " + skillElem.getTextContent() + " [" + prof + "]");
                        }
                        break;
                    }
                }
            }

            if (!found) {
                System.out.println("\nStudent with ID '" + searchId + "' not found.");
            }

        } catch (Exception e) {
            System.err.println("Error parsing XML: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private static String getTagValue(String tag, Element element) {
        NodeList nodeList = element.getElementsByTagName(tag);
        if (nodeList.getLength() > 0) {
            Node node = nodeList.item(0);
            return node.getTextContent();
        }
        return "";
    }
}


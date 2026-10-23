package sax;

import javax.xml.parsers.SAXParser;
import javax.xml.parsers.SAXParserFactory;
import org.xml.sax.Attributes;
import org.xml.sax.SAXException;
import org.xml.sax.helpers.DefaultHandler;
import java.io.File;
import java.util.Scanner;

/**
 * Practical 7B: Java SAX Parser for SkillBridge
 * Event-driven XML parsing, extracting student profile by ID.
 */
public class SAXParserDemo {

    public static void main(String[] args) {
        String xmlFilePath = "xml/student-profiles.xml";
        File xmlFile = new File(xmlFilePath);
        if (!xmlFile.exists()) {
            xmlFile = new File("../../xml/student-profiles.xml");
        }

        String targetId = "101";
        if (args.length > 0) {
            targetId = args[0].replaceAll("[^a-zA-Z0-9]", "").trim();
        } else {
            Scanner scanner = new Scanner(System.in);
            System.out.println("==================================================");
            System.out.println("SkillBridge XML SAX Parser — Practical 7");
            System.out.println("==================================================");
            System.out.print("Enter Student ID to search (e.g., 101, 103): ");
            if (scanner.hasNextLine()) {
                targetId = scanner.nextLine().replaceAll("[^a-zA-Z0-9]", "").trim();
            }
        }

        try {
            SAXParserFactory factory = SAXParserFactory.newInstance();
            SAXParser saxParser = factory.newSAXParser();

            StudentHandler handler = new StudentHandler(targetId);
            saxParser.parse(xmlFile, handler);

            if (!handler.isFound()) {
                System.out.println("\nStudent with ID '" + targetId + "' not found.");
            }

        } catch (Exception e) {
            System.err.println("SAX Parsing Error: " + e.getMessage());
            e.printStackTrace();
        }
    }
}

class StudentHandler extends DefaultHandler {
    private String targetId;
    private boolean isTargetStudent = false;
    private boolean found = false;

    private StringBuilder currentText = new StringBuilder();
    private String currentProficiency = "";

    public StudentHandler(String targetId) {
        this.targetId = targetId;
    }

    public boolean isFound() {
        return found;
    }

    @Override
    public void startElement(String uri, String localName, String qName, Attributes attributes) throws SAXException {
        currentText.setLength(0);

        if (qName.equalsIgnoreCase("student")) {
            String id = attributes.getValue("id");
            if (id != null && id.equalsIgnoreCase(targetId)) {
                isTargetStudent = true;
                found = true;
                System.out.println("\n[Student Found via SAX]");
                System.out.println("ID: " + id);
            }
        } else if (qName.equalsIgnoreCase("skill") && isTargetStudent) {
            currentProficiency = attributes.getValue("proficiency");
        }
    }

    @Override
    public void characters(char[] ch, int start, int length) throws SAXException {
        currentText.append(ch, start, length);
    }

    @Override
    public void endElement(String uri, String localName, String qName) throws SAXException {
        if (!isTargetStudent) {
            return;
        }

        String content = currentText.toString().trim();

        if (qName.equalsIgnoreCase("name")) {
            System.out.println("Name:            " + content);
        } else if (qName.equalsIgnoreCase("email")) {
            System.out.println("Email:           " + content);
        } else if (qName.equalsIgnoreCase("college")) {
            System.out.println("College:         " + content);
        } else if (qName.equalsIgnoreCase("degree")) {
            System.out.print("Degree:          " + content);
        } else if (qName.equalsIgnoreCase("branch")) {
            System.out.println(" (" + content + ")");
        } else if (qName.equalsIgnoreCase("graduation_year")) {
            System.out.println("Graduation Year: " + content);
            System.out.println("Skills:");
        } else if (qName.equalsIgnoreCase("skill")) {
            System.out.println("  • " + content + " [" + currentProficiency + "]");
        } else if (qName.equalsIgnoreCase("student")) {
            isTargetStudent = false; // Finished reading target student
        }
    }
}


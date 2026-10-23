package xml;

import java.io.File;
import javax.xml.XMLConstants;
import javax.xml.transform.stream.StreamSource;
import javax.xml.validation.Schema;
import javax.xml.validation.SchemaFactory;
import javax.xml.validation.Validator;

/**
 * Practical 6: XML Validation against XSD Schema
 */
public class XMLValidator {
    public static void main(String[] args) {
        String xmlPath = "xml/student-profiles.xml";
        String xsdPath = "xml/student-profile.xsd";

        try {
            SchemaFactory factory = SchemaFactory.newInstance(XMLConstants.W3C_XML_SCHEMA_NS_URI);
            Schema schema = factory.newSchema(new File(xsdPath));
            Validator validator = schema.newValidator();
            validator.validate(new StreamSource(new File(xmlPath)));

            System.out.println("[SUCCESS] XML Validation: " + xmlPath + " is VALID against " + xsdPath);
        } catch (Exception e) {
            System.err.println("[FAILED] XML Validation: " + e.getMessage());
        }
    }
}

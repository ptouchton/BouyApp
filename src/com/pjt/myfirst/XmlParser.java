package com.pjt.myfirst;

import java.io.StringReader;
import java.util.ArrayList;

import javax.xml.parsers.ParserConfigurationException;
import javax.xml.parsers.SAXParser;
import javax.xml.parsers.SAXParserFactory;

import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;

public class XmlParser {
    
    private XMLReader initializeReader() throws ParserConfigurationException, SAXException {
        SAXParserFactory factory = SAXParserFactory.newInstance();
        // create a parser
        SAXParser parser = factory.newSAXParser();
        // create the reader (scanner)
        XMLReader xmlreader = parser.getXMLReader();
        return xmlreader;
    }
    
    public ArrayList<Bouy> parseGetObservationResponse(String xml) {
        
        try {
            
            XMLReader xmlreader = initializeReader();
            
            BouyListXmlHandler bouyListHandler = new BouyListXmlHandler();

            // assign our handler
            xmlreader.setContentHandler(bouyListHandler);
            // perform the synchronous parse
            xmlreader.parse(new InputSource(new StringReader(xml)));
            
            return bouyListHandler.retrieveStationList();
            
        } 
        catch (Exception e) {
            e.printStackTrace();
            return null;
        }
        
    }

}
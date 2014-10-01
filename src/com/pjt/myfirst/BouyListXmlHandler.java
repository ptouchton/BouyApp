package com.pjt.myfirst;
import java.util.ArrayList;

import org.xml.sax.Attributes;
import org.xml.sax.SAXException;
import org.xml.sax.helpers.DefaultHandler;

public class BouyListXmlHandler extends DefaultHandler {
    
    private StringBuffer buffer = new StringBuffer();
    
    private ArrayList<Bouy> bouyList;
    private Bouy bouy;
   
    
    @Override
    public void startElement(String namespaceURI, String localName,
            String qName, Attributes atts) throws SAXException {
        
    	
        buffer.setLength(0);
        try {
			if (localName.equals("ContextArray")){
				if (atts.getValue("id").equals("StationArray")){
				bouyList = new ArrayList<Bouy>();
				//blnEndThis = true;
				}
			}
			if (localName.equals("StationName")) {
			    bouy = new Bouy();
			}
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

    }
    
    @Override
    public void endElement(String uri, String localName, String qName)throws SAXException {
        
        //if (localName.equals("ContextArray")& blnEndThis) {
        if (localName.equals("ContextArray")) {
        	if(!bouyList.contains(bouy)){
				bouyList.add(bouy);	
        	}
        }
        else if (localName.equals("StationName")) {
            bouy.setName(buffer.toString());
        }
        else if (localName.equals("StationId")) {
        	String[] splitId = buffer.toString().split("\\:");
        	bouy.setId(splitId[(splitId.length-1)]);
        }
        
    }
    
    @Override
    public void characters(char[] ch, int start, int length) {
        buffer.append(ch, start, length);
    }
        
    public ArrayList<Bouy> retrieveStationList() {
        return bouyList;
    }
    
    
}

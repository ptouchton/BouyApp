package com.pjt.myfirst;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLConnection;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.NoSuchElementException;
import java.util.StringTokenizer;
import java.util.TimeZone;


import com.pjt.myfirst.R;

import android.app.Activity;
import android.app.ProgressDialog;
import android.os.Bundle;
import android.util.Log;
import android.widget.ListView;

public class BouyDetailActivity extends Activity {
	
	ArrayList<SpecDetail>      specDetails              = null;
	private ProgressDialog     progressDialog          = null; 
	private WaveDetailAdapter  detailsAdapter;
    private Runnable           viewDetails;
    private String             bouyId;
    
    //vals for convertUtcToLocal
    SimpleDateFormat df  = new SimpleDateFormat("MM/dd/yyyy HH:mm");
    SimpleDateFormat df2 = new SimpleDateFormat("MM/dd/yyyy HH:mm");
    String returnDate = "";
    TimeZone tzGmt   = TimeZone.getTimeZone("GMT");
    TimeZone tzLocal = TimeZone.getDefault();
    
    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
    
        //grab the bouy passed in from the bouylist selection
        bouyId = this.getIntent().getExtras().getString("id");
        
        setContentView(R.layout.main3);
        //setContentView(R.layout.list_rows);
        
        setTitle(getTitle()+ ": "+ bouyId);

        specDetails = new ArrayList<SpecDetail>();
        this.detailsAdapter = new WaveDetailAdapter(this,R.layout.list_rows,specDetails);
        ListView tv = (ListView)this.findViewById(R.id.mylist);
		tv.setAdapter(this.detailsAdapter);
        //setListAdapter(this.detailsAdapter);
        viewDetails = new Runnable(){
        	@Override
        	public void run(){
        		getWaveData();
        	}
        };
        
        Thread thread = new Thread(null,viewDetails,"MagnetBackground");
        thread.start();
        progressDialog = ProgressDialog.show(this, "Please wait...", "Retrieving bouy data...", true);

    
    
    
    }
    
    private void getWaveData(){
    	URLConnection conn = null;
    	String strUrl = "";
    	String line = "";
        URL url;
        
        int i = 0;
        
		try {
			
			strUrl = getResources().getString(R.string.NoaaUrl);
			strUrl += bouyId+".spec";
			Log.i("DetailsUrl","Url: "+strUrl);
			url = new URL(strUrl);
			conn = url.openConnection();
			BufferedReader rd = new BufferedReader(new InputStreamReader(conn.getInputStream()));
			SpecDetail specDetail = new SpecDetail();
			specDetails = new ArrayList<SpecDetail>();
			
			//read d response till d end
			while ((line = rd.readLine()) != null) {
		
				i++; //SkipHeaders
				if (i > 2) {

					specDetail = BuildTable(line);
					specDetails.add(specDetail);
					
				}
				
	        }
			
		} catch (MalformedURLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		//show the wave data
		runOnUiThread(returnRes);
    	
    }
    
    private Runnable returnRes = new Runnable(){
    	
    	@Override
        public void run() {
            if(specDetails != null &&specDetails.size() > 0){
            	detailsAdapter.notifyDataSetChanged();
                for(int i=0;i<specDetails.size();i++)
                	detailsAdapter.add(specDetails.get(i));
            }
            progressDialog.dismiss();
             detailsAdapter.notifyDataSetChanged();
        }    	
    	
    };
   
    private SpecDetail BuildTable(String strInput){
    	
    	SpecDetail specDetail = new SpecDetail();
    	StringTokenizer stoke = new StringTokenizer(strInput);

    	while (stoke.hasMoreElements()){

            try {
				specDetail.setSpecYear(stoke.nextToken());
				specDetail.setSpecMonth(stoke.nextToken());
				specDetail.setSpecDay(stoke.nextToken());
				specDetail.setSpecHour(stoke.nextToken());
				specDetail.setSpecMinute(stoke.nextToken());
				//convert the utc to local timezone
				specDetail.setSpecLocalDateTime(this.convertUtcToLocal(specDetail));
				specDetail.setSpecWvht(Float.parseFloat(stoke.nextToken()));
				specDetail.setSpecSwh(Float.parseFloat(stoke.nextToken()));
				specDetail.setSpecSwp(Float.parseFloat(stoke.nextToken()));
				specDetail.setSpecWwh(Float.parseFloat(stoke.nextToken()));
				specDetail.setSpecWwp(Float.parseFloat(stoke.nextToken()));
				specDetail.setSpecSwd(stoke.nextToken());
				specDetail.setSpecWwd(stoke.nextToken());
				specDetail.setSpecSteepness(stoke.nextToken());
				specDetail.setSpecApd(Float.parseFloat(stoke.nextToken()));
				specDetail.setSpecMwd(Float.parseFloat(stoke.nextToken()));
			} catch (NumberFormatException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			} catch (NoSuchElementException e){
				e.printStackTrace();
			}
			
    			
    	}
    	
    	return specDetail;
    	
    }
    
    private String convertUtcToLocal(SpecDetail specDetail){
        String s = specDetail.getSpecUtcDateTime();
        
        try {
        	
        	df.setTimeZone(tzGmt);
        	Date dateTest = (Date)df.parseObject(s);

        	df2.setTimeZone(tzLocal);
        	returnDate = df2.format(dateTest);
            
        } catch (ParseException e) {
            e.printStackTrace();
        }
		return returnDate;

    }

  }
    


    

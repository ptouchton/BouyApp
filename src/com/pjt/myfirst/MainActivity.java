package com.pjt.myfirst;


import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.util.EntityUtils;


import android.app.ListActivity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.location.Criteria;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.os.Bundle;
import android.os.Handler;
import android.os.Message;
import android.preference.PreferenceManager;
import android.util.Log;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.widget.Toast;

public class MainActivity extends ListActivity{
	
	private ProgressDialog    progressDialog = null;
    private LocationManager   locManager;
    //private Runnable          listBouys;
    ArrayList<Bouy>       bouyList       = null;
    private BouyListAdapter   bouyListAdapter;
    
    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        setContentView(R.layout.main);
        
        progressDialog = new ProgressDialog(this);
        progressDialog.setIndeterminate(true);
        
        bouyList = new ArrayList<Bouy>();
        this.bouyListAdapter = new BouyListAdapter(this,R.layout.bouy_list,bouyList);
		setListAdapter(this.bouyListAdapter);
		 
        Location location = getLocation();
			/*locManager.requestLocationUpdates(LocationManager.GPS_PROVIDER, 0, 0,
					locationListener);
			*/		
		if (location != null){

			ListBouyThread thread = new ListBouyThread(handler,location);
			thread.start();

		}
		else{
			Toast toast = Toast.makeText(this.getApplicationContext(), "Current Location Not available", Toast.LENGTH_SHORT);
			toast.show();
		}
		
    }//onCreate()
    
	// Define the Handler that receives messages from the thread and update the
	// progress
	private Handler handler = new Handler() {
		public void handleMessage(Message msg) {
			if (msg.arg1 == 1){
				progressDialog.setMessage("Retrieving Bouys...");
				progressDialog.show();
			}
		}
	};//handler

	private void getBouyList(Location location) {

		try {

			if (location != null) {

				SharedPreferences pref = PreferenceManager.getDefaultSharedPreferences(getApplicationContext());
				String distance = pref.getString("editDistancePref", "50");
				
				//get the bounding box coordinates from the pref distance
				//test code
				GeoLocation myLocation = GeoLocation.fromDegrees(location.getLatitude(),
						location.getLongitude());

				GeoLocation[] myCoordinates =  
					myLocation.boundingCoordinates(Double.parseDouble(distance), 6371);

				if (myCoordinates.length > 0){

					String strUrl = getResources().getString(R.string.coordinatesearchUrl);
					strUrl += "&" + getResources().getString(R.string.searchrequest);
					strUrl += "&" + getResources().getString(R.string.searchservice);
					strUrl += "&" + getResources().getString(R.string.searchoffering);
					strUrl += "&" + getResources().getString(R.string.searchfeature);
					strUrl += myCoordinates[0].getLongitudeInDegrees();
					strUrl += "," + myCoordinates[0].getLatitudeInDegrees();
					strUrl += "," + myCoordinates[1].getLongitudeInDegrees();
		 			strUrl += "," + myCoordinates[1].getLatitudeInDegrees();
					strUrl += "&" + getResources().getString(R.string.searchobservedproperty);
					strUrl += "&" + getResources().getString(R.string.searchresponseformat);
					strUrl += 
						URLEncoder.encode(getResources().getString(R.string.searchschemaname),"UTF-8");

					URL url = new URL(strUrl);
					DefaultHttpClient client = new DefaultHttpClient();  
					HttpGet getRequest = new HttpGet(url.toString());

					HttpResponse getResponse = client.execute(getRequest);
					final int statusCode = getResponse.getStatusLine().getStatusCode();
					if (statusCode != HttpStatus.SC_OK) {
						Log.w(getClass().getSimpleName(), "Error " + statusCode + " for URL " + strUrl);
					}

					HttpEntity getResponseEntity = getResponse.getEntity();

					if (getResponseEntity != null) {

						String response = EntityUtils.toString(getResponseEntity);
						XmlParser xmlParser = new XmlParser();
						bouyList = xmlParser.parseGetObservationResponse(response);
					}

				}
			}
			} catch (MalformedURLException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			} catch (IOException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
				
			} catch (Exception e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			this.runOnUiThread(returnRes);
	}//getBouyList


	private Location getLocation(){

		locManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
		
        Criteria criteria = new Criteria();
		String provider = locManager.getBestProvider(criteria, false);
		
		//Location location = locManager.getLastKnownLocation(LocationManager.GPS_PROVIDER);
		Location location = locManager.getLastKnownLocation(provider);
		
		return location;
	}
    private final LocationListener locationListener = new LocationListener() {
        public void onLocationChanged(Location location) {
        	//ListBouyThread thread = new ListBouyThread(handler,location);
            //thread.start();
        }

        public void onProviderDisabled(String provider) {
            //updateWithNewLocation(null);
        }

        public void onProviderEnabled(String provider) {
        }

        public void onStatusChanged(String provider, int status, Bundle extras) {
        }
    };//locationListener

    private Runnable returnRes =  new Runnable() {
    	@Override
        public void run(){
            try {
				if(bouyList != null && bouyList.size() > 0){
					bouyListAdapter.clear();
				    bouyListAdapter.notifyDataSetChanged();
				    for(int i=0;i<bouyList.size();i++)
				    bouyListAdapter.add(bouyList.get(i));
				}
				progressDialog.dismiss();
				bouyListAdapter.notifyDataSetChanged();
			} catch (Exception e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
    	}
			
        };//returnRes
    
    /* Nested class that handles the progressbar message and calling
       the get bouy method  */
    private class ListBouyThread extends Thread {
            Handler handler;
            final static int STATE_RUNNING = 1;
            
            Location location;
            
           
            ListBouyThread(Handler h,Location l) {
                handler = h;
                location = l;
            }
           
            public void run() {
                    try {
						Message msg = handler.obtainMessage();
						msg.arg1 = STATE_RUNNING;
						handler.sendMessage(msg);
						getBouyList(location);
					} catch (Exception e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}
        
            }
          
        }//ListBouyThread
        
    @Override
	public boolean onCreateOptionsMenu(Menu menu) {
		MenuInflater inflater = getMenuInflater();
		inflater.inflate(R.menu.menu, menu);
		return true;
	}
    
 // This method is called once the menu is selected
	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		switch (item.getItemId()) {
		// We have only one menu option
		case R.id.preferences:
			// Launch Preference activity
			Intent i = new Intent(this.getApplicationContext(), PreferencesActivity.class);
			startActivity(i);
			break;
		case R.id.refresh:
			//restart the bouylist search
			Location location = getLocation();
			ListBouyThread thread = new ListBouyThread(handler,location);
            thread.start();

		}
		return true;
	}
    
    }


package com.pjt.myfirst;

import java.util.ArrayList;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

public class WaveDetailAdapter extends ArrayAdapter<SpecDetail> {

    private ArrayList<SpecDetail> items;
    private static final float meters2Feet = (float) 3.2808399;
    private float conversionValue = meters2Feet;

    public WaveDetailAdapter(Context context, int textViewResourceId, ArrayList<SpecDetail> items) {
            super(context, textViewResourceId, items);
            this.items = items;
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
            View v = convertView;
            Float result;
            
            if (v == null) {
                LayoutInflater vi = (LayoutInflater)getContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                v = vi.inflate(R.layout.list_rows, null);
            }
            
            SpecDetail o = items.get(position);
            
            if (o != null) {
            	
            	 
                    try {
						TextView date = (TextView) v.findViewById(R.id.date);
						//TextView time = (TextView) v.findViewById(R.id.time);
						TextView wvht = (TextView) v.findViewById(R.id.wvht);
						TextView swh  = (TextView) v.findViewById(R.id.swh);
						TextView swp  = (TextView) v.findViewById(R.id.swp);
						TextView wwh  = (TextView) v.findViewById(R.id.wwh);
						TextView wwp  = (TextView) v.findViewById(R.id.wwp);
						TextView swd  = (TextView) v.findViewById(R.id.swd);
						TextView wwd  = (TextView) v.findViewById(R.id.wwd);
						TextView stp  = (TextView) v.findViewById(R.id.steepness);
						TextView apd  = (TextView) v.findViewById(R.id.apd);
						TextView mwd  = (TextView) v.findViewById(R.id.mwd);
						
						/*if (date != null) {
						      date.setText(o.getSpecDate());
						}
						if (time != null) {
						    time.setText(o.getSpecTime());
              }
						 */
						if (date !=null){
							date.setText(o.getSpecLocalDateTime());
						}
						if (wvht != null) {
							result = (o.getSpecWvht()*conversionValue);
							wvht.setText(String.format("%.1f",result));
						}
						if (swh != null) {
							result = (o.getSpecSwh()*conversionValue);
							swh.setText(String.format("%.1f",result));
						}
						if (swp != null) {
							swp.setText(Float.toString(o.getSpecSwp()));
						}
						if (wwh != null) {
							result = (o.getSpecWwh()*conversionValue);
							wwh.setText(String.format("%.1f",result));
						}
						if (wwp != null) {
							wwp.setText(Float.toString(o.getSpecWwp()));
						}
						if (swd != null) {
							swd.setText(o.getSpecSwd());
						}
						if (wwd != null) {
							wwd.setText(o.getSpecWwd());
						}
						if (stp != null) {
							stp.setText(o.getSpecSteepness());
						}
						if (apd != null) {
							apd.setText(Float.toString(o.getSpecApd()));

						}
						if (mwd != null) {
							mwd.setText(String.format("%.0f",o.getSpecMwd()));

						}
					} catch (Exception e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}

            
            
            
            }
            return v;
    }
}

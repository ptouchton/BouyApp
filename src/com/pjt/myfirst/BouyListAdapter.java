package com.pjt.myfirst;

import java.util.ArrayList;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

public class BouyListAdapter extends ArrayAdapter<Bouy> {

    private ArrayList<Bouy> items;
    private TextView name = null;
    private TextView desc = null;

    public BouyListAdapter(Context context, int textViewResourceId, ArrayList<Bouy> items) {
            super(context, textViewResourceId, items);
            this.items = items;
    }

       
    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
            View v = convertView;
                        
            if (v == null) {
                LayoutInflater vi = (LayoutInflater)getContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                v = vi.inflate(R.layout.bouy_list, null);
            }
            
            Bouy o = items.get(position);
            
            if (o != null) {
            	
            	 
                    try {
						name = (TextView) v.findViewById(R.id.bouyname);
						//TextView time = (TextView) v.findViewById(R.id.time);
						desc = (TextView) v.findViewById(R.id.bouydesc);

						if (name !=null){
							name.setText(o.getId());
						}
						if (desc != null){
							desc.setText(o.getName());
						}
					} catch (Exception e) {
						// TODO Auto-generated catch block
						e.printStackTrace();
					}

            
            
            
            }
            v.setOnClickListener(new BouyListOnClickListener(position,name.getText().toString()));
            //set the onClickListener)
            //v.setOnClickListener(new BouyListOnClickListener(position));
            return v;
    }
}

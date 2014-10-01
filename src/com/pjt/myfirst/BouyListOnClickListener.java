package com.pjt.myfirst;

import android.content.Intent;
import android.util.Log;
import android.view.View;
import android.view.View.OnClickListener;

public class BouyListOnClickListener implements OnClickListener{
	
	private int position;
	private String id;
	
	BouyListOnClickListener(int position,String id){
	  this.position = position;	
	  this.id       = id;
	}
	
	@Override
	public void onClick(View view){
		Log.i("OnClick", "BouyItemClick at position/id: " + position+"/"+id);
		Intent intent = new Intent(view.getContext(), BouyDetailActivity.class);
		intent.putExtra("id", this.id);
		view.getContext().startActivity(intent);
		
	}

}

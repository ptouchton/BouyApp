package com.pjt.myfirst;


public class SpecDetail {
	private String  specYear;
	private String  specMonth;
	private String  specDay;
	private String  specHour;
	private String  specMinute;
	private String   specDate;
	private float     specWvht;
	private float     specSwh;
	private float     specSwp;
	private float     specWwh;
	private float     specWwp;
	private String  specSwd;
	private String  specWwd;
	private String  specSteepness;
	private float     specApd;
	private float     specMwd;
	private String  specTime;
	private String  specLocalDateTime;
	
	
	public String getSpecYear() {
		return specYear;
	}
	public void setSpecYear(String specYear) {
		this.specYear = specYear;
	}
	public String getSpecMonth() {
		return specMonth;
	}
	public void setSpecMonth(String specMonth) {
		this.specMonth = specMonth;
	}
	public String getSpecDay() {
		return specDay;
	}
	public void setSpecDay(String specDay) {
		this.specDay = specDay;
	}
	public String getSpecHour() {
		return specHour;
	}
	public void setSpecHour(String specHour) {
		this.specHour = specHour;
	}
	public String getSpecMinute() {
		return specMinute;
	}
	public void setSpecMinute(String specMinute) {
		this.specMinute = specMinute;
	}
	public float getSpecWvht() {
		return specWvht;
	}
	public void setSpecWvht(float specWvht) {
		this.specWvht = specWvht;
	}
	public float getSpecSwh() {
		return specSwh;
	}
	public void setSpecSwh(float specSwh) {
		this.specSwh = specSwh;
	}
	public float getSpecSwp() {
		return specSwp;
	}
	public void setSpecSwp(float specSwp) {
		this.specSwp = specSwp;
	}
	public float getSpecWwh() {
		return specWwh;
	}
	public void setSpecWwh(float specWwh) {
		this.specWwh = specWwh;
	}
	public float getSpecWwp() {
		return specWwp;
	}
	public void setSpecWwp(float specWwp) {
		this.specWwp = specWwp;
	}
	public String getSpecSwd() {
		return specSwd;
	}
	public void setSpecSwd(String specSwd) {
		this.specSwd = specSwd;
	}
	public String getSpecWwd() {
		return specWwd;
	}
	public void setSpecWwd(String specWwd) {
		this.specWwd = specWwd;
	}
	public String getSpecSteepness() {
		return specSteepness;
	}
	public void setSpecSteepness(String specSteepness) {
		this.specSteepness = specSteepness;
	}
	public float getSpecApd() {
		return specApd;
	}
	public void setSpecApd(float specApd) {
		this.specApd = specApd;
	}
	public float getSpecMwd() {
		return specMwd;
	}
	public void setSpecMwd(float specMwd) {
		this.specMwd = (int)specMwd;
	}
	public String getSpecDate() {
		specDate = specLocalDateTime; 
		return  specDate;
	}
	public String getSpecTime() {
		specTime = specHour+":"+specMinute; 
		return  specTime;
	}	
	public String getSpecUtcDateTime() {
		return this.getSpecMonth()  +"/"+
		       this.getSpecDay()    +"/"+
		       this.getSpecYear()   +" "+
		       this.getSpecHour()   +":"+
		       this.getSpecMinute();
	}
	public String getSpecLocalDateTime() {
		return specLocalDateTime;
	}
	public void setSpecLocalDateTime(String specLocalDateTime) {
		this.specLocalDateTime = specLocalDateTime;
	}	

}

package com.pjt.myfirst;

public class BouyList {
	
	@Override
    public boolean equals(Object obj) {
        if (!(obj instanceof BouyList))
           return false;

        return stationName.equals(((BouyList) obj).getStationName());
    }

    @Override
    public int hashCode() {
        return (stationName == null) ? 0 : stationName.hashCode();
    }

	private String stationName;
	private String stationLink;

	public String getStationName() {
		return stationName;
	}
	public void setStationName(String stationName) {
		this.stationName = stationName;
	}
	public String getStationLink() {
		return stationLink;
	}
	public void setStationLink(String stationLink) {
		this.stationLink = stationLink;
	}

}

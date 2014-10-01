package com.pjt.myfirst;

public class Bouy {
	
	@Override
    public boolean equals(Object obj) {
        if (!(obj instanceof Bouy))
           return false;

        return id.equals(((Bouy) obj).getId());
    }

    @Override
    public int hashCode() {
        return (id == null) ? 0 : id.hashCode();
    }

	private String name;
	private String id;
	private String latitude;
	private String longitude;
	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public String getId() {
		return id;
	}

	public void setId(String id) {
		this.id = id;
	}

	public String getLatitude() {
		return latitude;
	}

	public void setLatitude(String latitude) {
		this.latitude = latitude;
	}

	public String getLongitude() {
		return longitude;
	}

	public void setLongitude(String longitude) {
		this.longitude = longitude;
	}

	

}

CREATE VIEW `facility_book_view` 
	AS SELECT fb.*, u.user_name, u.full_name, u.user_email, u.contact_no, u.id_group, u.id_department, u.nric,   
        f.description, f.period_duration, f.max_period, f.lead_time,
        l.*, location_name facility_no 
        FROM facility_book fb 
        LEFT JOIN facility f ON fb.id_facility = f.id_facility 
        LEFT JOIN location l ON f.id_location = l.id_location
        LEFT JOIN user u ON u.id_user = fb.id_user 
        

CREATE VIEW `calendar_view` 
	AS SELECT ce.*, u.user_name, u.full_name, u.user_email, u.contact_no, u.id_group, u.id_department, u.nric,   
        l.location_name, l.location_desc  
        FROM calendar_events ce 
        LEFT JOIN location l ON ce.id_location = l.id_location
        LEFT JOIN user u ON u.id_user = ce.id_user 
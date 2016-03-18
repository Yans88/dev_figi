
alter table notification_message add msg_type enum('email','sms') after msg_content;
alter table fault_report change fault_location id_location int not null;

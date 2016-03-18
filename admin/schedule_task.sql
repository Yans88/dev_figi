create table schedule_task( 
    id_task int not null auto_increment primary key, 
    task_name varchar(32) not null, 
    task_path varchar(64) not null,
    task_status enum('disable','enable') not null,
    task_period enum('monthly', 'weekly', 'daily') not null,
    task_time time not null default '00:00:00',
    create_time timestamp not null
);
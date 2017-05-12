create table evm_event(
	id int unsigned not null primary key auto_increment,
	name varchar(100) not null default '',
	city varchar(20) not null default '',
	associated_city varchar(30) not null default '',
	associated_year varchar(5) not null default '',
	store varchar(8) not null default '0',
	georegion char(4) not null default '0',
	location varchar(100) not null default '',
	event_date char(8) not null default '',
	month char(6) not null default '',
	type varchar(10) not null default '',
	format varchar(20) not null default '',
	description text default '',
	sanction_number varchar(15) not null default '',
	reserved_1 varchar(255) default '',
	reserved_2 varchar(255) default '',
	reserved_3 varchar(255) default ''
) engine InnoDB charset utf8;

create table evm_deletedevent(
	id int unsigned not null primary key auto_increment,
	original_id int unsigned not null,
	name varchar(100) not null default '',
	city varchar(20) not null default '',
	associated_city varchar(30) not null default '',
	associated_year varchar(5) not null default '',
	store varchar(8) not null default '0',
	georegion char(4) not null default '0',
	location varchar(100) not null default '',
	event_date char(8) not null default '',
	month char(6) not null default '',
	type varchar(10) not null default '',
	format varchar(20) not null default '',
	description text default '',
	sanction_number varchar(15) not null default '',
	reserved_1 varchar(255) default '',
	reserved_2 varchar(255) default '',
	reserved_3 varchar(255) default ''
) engine InnoDB charset utf8;

create table evm_store(
	id int unsigned not null primary key auto_increment,
	name varchar(100) not null default '',
	city varchar(20) not null default '',
	georegion char(4) not null default '0',
	location varchar(100) not null default '',
	contact text default '',
	email text default '',
	orgid int,
	businessid int,
	latitude decimal(9,6),
	longitude decimal(9,6),
	countrycode varchar(3),
	provincecode varchar(3),
	reserved_1 varchar(255) default '',
	reserved_2 varchar(255) default ''
) engine InnoDB charset utf8;

create table evm_travelguide(
	id int unsigned not null primary key auto_increment,
	store varchar(8) not null default '0',
	content text default '',
	author int not null
) engine InnoDB charset utf8;

create table evm_tempevent(
	id int unsigned not null primary key auto_increment,
	action varchar(10) not null,
	original_id int,
	name varchar(100) not null default '',
	city varchar(20) not null default '',
	associated_city varchar(30) not null default '',
	associated_year varchar(5) not null default '',
	store varchar(8) not null default '0',
	georegion char(4) not null default '0',
	location varchar(100) not null default '',
	event_date char(8) not null default '',
	type varchar(10) not null default '',
	format varchar(20) not null default '',
	description text default '',
	sanction_number varchar(15) not null default '',
	reserved_1 varchar(255) default '',
	reserved_2 varchar(255) default ''
) engine InnoDB charset utf8;

create table evm_customevent(
	id int unsigned not null primary key auto_increment,
	event_id int not null,
	judge_id int not null,
	create_date char(8) not null,
	reserved varchar(255) default ''
) engine InnoDB charset utf8;

create table evm_announcement(
	id int unsigned not null primary key auto_increment,
	textbody text not null,
	last_modify_date char(8) not null,
	last_modify_judge int not null,
	reserved varchar(255) default ''
) engine InnoDB charset utf8;

INSERT INTO `eventminder`.`evm_announcement` (`id`, `textbody`, `last_modify_date`, `last_modify_judge`, `reserved`) VALUES (NULL, '123', '19700101', '1', '');

create table evm_ptqseason(
	id int unsigned not null primary key auto_increment,
	pt_city varchar(100) not null,
	start_year char(4) not null,
	reserved varchar(255) default ''
) engine InnoDB charset utf8;

create table evm_user(
	id int unsigned not null primary key auto_increment,
	username varchar(20) not null default '',
	fullname varchar(30) not null default '',
	city varchar(20) not null default '',
	level varchar(2) not null default '0',
	dci varchar(10) not null default '',
	role_admin varchar(2) not null default '0',
	role_1 varchar(20) default '',
	role_2 varchar(20) default '',
	role_3 varchar(20) default '',
	password varchar(50) not null default '',
	lastlogin_ip varchar(20) default '',
	lastlogin_time varchar(20) default '',
	reserved_1 varchar(255) default '',
	reserved_2 varchar(255) default ''
) engine InnoDB charset utf8;

create table evm_eventstaff(
	id int unsigned not null primary key auto_increment,
	event_id int not null,
	judge_id int not null,
	is_hj boolean not null,
	pickup_date char(8) not null,
	reserved varchar(255) default ''
) engine InnoDB charset utf8;

create table evm_regquestion(
	id int unsigned not null primary key auto_increment,
	question varchar(255) not null default '',
	answer varchar(255) not null default ''
) engine InnoDB charset utf8;

create table evm_georegion(
	id int unsigned not null primary key auto_increment,
	region varchar(20) not null default '',
	citylist text not null default ''
) engine InnoDB charset utf8;

create view evm_eventjudge as 
	select A.id, 
		A.name, 
		A.city,
		A.associated_city,
		A.store,
		A.georegion,
		A.location, 
		A.type, 
		A.format, 
		A.event_date, 
		A.description, 
		B.fullname, 
		B.level, 
		C.judge_id, 
		C.is_hj, 
		C.pickup_date 
	from evm_event A,
	 evm_eventstaff C 
	left join evm_user B 
	on C.judge_id = B.id 
	where A.id = C.event_id;

create table evm_favstore(
	id int unsigned not null primary key auto_increment,
	judge_id int not null,
	store_id int not null
) engine InnoDB charset utf8;

create view evm_judgestore as 
	select A.id, 
		A.name,
		A.city,
		B.fullname,
		C.judge_id
	from evm_store A,
		evm_favstore C 
	left join evm_user B 
	on C.judge_id = B.id 
	where A.id = C.store_id;

create table evm_adminlog(
	id int unsigned not null primary key auto_increment,
	user_id int not null,
	user_name varchar(30) not null default '',
	op_type varchar(20) not null default '',
	op_detail varchar(200) not null default '',
	op_ip varchar(20) default '',
	op_time varchar(20) default '',
	reserved varchar(255) default ''
) engine InnoDB charset utf8;

create table evm_l2checklist(
	id int unsigned not null primary key auto_increment,
	judge_id int not null,
	item_type varchar(10) not null default '',
	item_date char(8) not null default '',
	item_subject varchar(100) not null default '',
	item_body text not null default '',
	reserved varchar(255) default ''
) engine InnoDB charset utf8;

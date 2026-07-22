create database if not exists inventory_management_system;

use inventory_management_system;

create table users(
	id int auto_increment primary key,
    username varchar(50) unique not null,
    password varchar(30) not null,
    full_name varchar(50) not null,
    email varchar(50),
    role enum('admin','staff') not null default 'admin',
    created_at timestamp,
    updated_at timestamp
);


create table suppliers(
	id int auto_increment primary key,
    name varchar(150) not null,
    contact_person varchar(100),
    email varchar(100),
    phone varchar(20),
    address varchar(50),
    created_at timestamp,
    updated_at timestamp
);


create table products (
	id int auto_increment primary key,
    name varchar(150) not null,
    description text,
    category varchar(100),
    price decimal(10,2) not null default 0.00,
    quantity int not null default 0,
    low_stock_threshold int not null default 10,
    supplier_id int,
    created_at timestamp,
    updated_at timestamp,
    foreign key (supplier_id) references suppliers(id) on delete set null
);


create table txns (
	id int not null,
    product_id int not null,
    type enum('in','out') not null,
    quantity int not null,
    unit_price decimal(10,2),
    total_price decimal(10,2),
    notes text,
    user_id int,
    txn_date timestamp,
    foreign key (product_id) references products(id) on delete cascade,
    foreign key (user_id) references users(id) on delete set null
);


insert into users (username, password, full_name, email, role)
values ('admin', 'Admin123', 'Administrator', 'admin63@gmail.com', 'admin');


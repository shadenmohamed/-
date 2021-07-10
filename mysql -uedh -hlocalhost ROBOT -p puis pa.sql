mysql -uedh -hlocalhost ROBOT -p   puis password edh

CREATE TABLE robot_infos (
id INT(6) UNSIGNED AUTO_INCREMENT,
dt TIMESTAMP,
source INT(6) UNSIGNED,
alert INT(6) UNSIGNED,
no_picture INT(6) UNSIGNED,
motor_state INT(6) UNSIGNED,
direction INT(6) UNSIGNED,
obstacle_status INT(6) UNSIGNED,
distance INT(6) UNSIGNED,
temperature INT(6) UNSIGNED,
humidity INT(6) UNSIGNED,
brightness INT(6) UNSIGNED,
noise INT(6) UNSIGNED,
PRIMARY KEY (id)
);
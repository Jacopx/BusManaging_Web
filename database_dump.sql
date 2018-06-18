CREATE TABLE Users
(
    user varchar(50) PRIMARY KEY NOT NULL,
    pass varchar(255) NOT NULL
);
INSERT INTO Users (user, pass) VALUES ('u1@p.it', '$2y$10$RgTgAT6Eojkt5GZwvisbn.bdAfri9GQFU3c0KqtbACUS7YVtOexW6');
INSERT INTO Users (user, pass) VALUES ('u2@p.it', '$2y$10$mUnTmU/FGYjPmWby9oWTfOJp2A939fCrKsXzUoDTK.r6kHPSYbir6');
INSERT INTO Users (user, pass) VALUES ('u3@p.it', '$2y$10$UphD9Lrdj816MvmXERjmr.//wSfZqEbpxRhzAZAEZ34ZgUPYH9zVu');
INSERT INTO Users (user, pass) VALUES ('u4@p.it', '$2y$10$8GDo9ZyBfRr8YLB1tHnIiOeGF2J5AtXpspZ0TQk6JCtVz2jqs4r6q');

CREATE TABLE Reservations
(
    user varchar(64) PRIMARY KEY NOT NULL,
    seats int(11) NOT NULL,
    start varchar(255) NOT NULL,
    end varchar(255) NOT NULL
);
INSERT INTO Reservations (user, seats, start, end) VALUES ('u1@p.it', 1, 'AA', 'FF');
INSERT INTO Reservations (user, seats, start, end) VALUES ('u2@p.it', 1, 'BB', 'EE');
INSERT INTO Reservations (user, seats, start, end) VALUES ('u3@p.it', 1, 'DD', 'EE');
INSERT INTO Reservations (user, seats, start, end) VALUES ('u4@p.it', 3, 'GG', 'HH');
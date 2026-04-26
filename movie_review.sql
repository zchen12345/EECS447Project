-- Movie Review Database Schema

-- User Table
--

DROP TABLE IF EXISTS Users;
CREATE TABLE IF NOT EXISTS Users (
  user_id INT AUTO_INCREMENT NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  PRIMARY KEY (user_id)
);
INSERT INTO Users (email, password, username)
VALUES ('john.doe@lmail.com', 'idkidc', 'GetLucky');
INSERT INTO Users (email, password, username)
VALUES ('jane.smith@lmail.com', 'Ilovemath', 'JanSm');
INSERT INTO Users (email, password, username)
VALUES ('bob.johnson@lmail.com', 'rockyou', 'real_name');
INSERT INTO Users (email, password, username)
VALUES ('sam@lmail.com', 'samlock', 'thatmoviesuck');
INSERT INTO Users (email, password, username)
VALUES ('tom@lmail.com', 'password123', 'LoveMovies');
INSERT INTO Users (email, password, username)
VALUES ('evan@lmail.com', 'notsecurepassword1', 'Evan123');
-- Genre Table
--

DROP TABLE IF EXISTS Genre;
CREATE TABLE IF NOT EXISTS Genre (
  genre_id INT AUTO_INCREMENT NOT NULL,
  name VARCHAR(50) NOT NULL UNIQUE,
  PRIMARY KEY (genre_id)
);
INSERT INTO Genre (name)
VALUES ('Action'), ('Comedy'), ('Drama'), ('Horror'), ('Sci-Fi');
-- Movie Table
--

DROP TABLE IF EXISTS Movie;
CREATE TABLE IF NOT EXISTS Movie (
  movie_id INT AUTO_INCREMENT NOT NULL,
  title VARCHAR(100) NOT NULL,
  release_date DATE DEFAULT NULL,
  genre_id INT NOT NULL,
  PRIMARY KEY (movie_id),
  FOREIGN KEY (genre_id) REFERENCES Genre(genre_id)
);
INSERT INTO Movie (title, release_date, genre_id)
VALUES ('Inception', '2010-07-16', 5),
       ('The Dark Knight', '2008-07-18', 1),
       ('Forrest Gump', '1994-07-06', 3),
       ('The Hangover', '2009-06-05', 2),
       ('Get Out', '2017-02-24', 4);
-- Reviews Table
--

DROP TABLE IF EXISTS Reviews;
CREATE TABLE IF NOT EXISTS Reviews (
  review_id INT AUTO_INCREMENT NOT NULL,
  user_id INT NOT NULL,
  movie_id INT NOT NULL,
  rating INT NOT NULL,
  comment TEXT DEFAULT NULL,
  PRIMARY KEY (review_id),
  FOREIGN KEY (user_id) REFERENCES Users(user_id),
  FOREIGN KEY (movie_id) REFERENCES Movie(movie_id)
);
INSERT INTO Reviews (user_id, movie_id, rating, comment)
VALUES (1, 1, 5, 'Amazing movie with a mind-bending plot!'),
       (2, 2, 4, 'Great action and a compelling story.'),
       (3, 3, 5, 'A heartwarming tale with fantastic performances.'),
       (4, 4, 3, 'Funny but a bit predictable.'),
       (5, 5, 4, 'A unique blend of horror and social commentary.'),
       (6, 1, 4, 'Inception was visually stunning and thought-provoking.'),
       (6, 2, 5, 'The Dark Knight is a masterpiece of superhero cinema!'),
       (2, 3, 4, 'Forrest Gump is a touching story with great performances.'),
       (3, 4, 2, 'The Hangover was funny but not my favorite comedy.'),
       (5, 5, 5, 'Get Out is a brilliant horror film with a powerful message.');

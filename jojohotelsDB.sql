CREATE DATABASE JojoHotelsDB;

USE JojoHotelsDB;

-- Create Users table
CREATE TABLE Users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    FullName VARCHAR(100),
    PhoneNumber VARCHAR(20),
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    verification_token VARCHAR(64),
    is_verified TINYINT(1) DEFAULT 0
);

-- Create RoomTypes table
CREATE TABLE RoomTypes (
    RoomTypeID INT PRIMARY KEY AUTO_INCREMENT,
    TypeName VARCHAR(50) NOT NULL,
    Description TEXT,
    BasePrice DECIMAL(10,2) NOT NULL,
    Capacity INT NOT NULL
);

-- Create Rooms table
CREATE TABLE Rooms (
    RoomID INT PRIMARY KEY AUTO_INCREMENT,
    RoomNumber VARCHAR(10) NOT NULL UNIQUE,
    RoomTypeID INT,
    FloorNumber INT,
    Status VARCHAR(20) DEFAULT 'Available',
    FOREIGN KEY (RoomTypeID) REFERENCES RoomTypes(RoomTypeID)
);

-- Create Bookings table
CREATE TABLE Bookings (
    BookingID INT PRIMARY KEY AUTO_INCREMENT,
    id INT,
    RoomID INT,
    CheckInDate DATE NOT NULL,
    CheckOutDate DATE NOT NULL,
    TotalPrice DECIMAL(10,2) NOT NULL,
    NumGuests INT NOT NULL,
    BookingStatus VARCHAR(20) DEFAULT 'Pending',
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id) REFERENCES Users(id),
    FOREIGN KEY (RoomID) REFERENCES Rooms(RoomID)
);

-- Create MenuCategories table
CREATE TABLE MenuCategories (
    CategoryID INT PRIMARY KEY AUTO_INCREMENT,
    CategoryName VARCHAR(50) NOT NULL,
    Description TEXT
);

-- Create MenuItems table
CREATE TABLE MenuItems (
    ItemID INT PRIMARY KEY AUTO_INCREMENT,
    CategoryID INT,
    ItemName VARCHAR(100) NOT NULL,
    Description TEXT,
    Price DECIMAL(10,2) NOT NULL,
    ImageURL VARCHAR(255),
    IsAvailable BOOLEAN DEFAULT 1,
    FOREIGN KEY (CategoryID) REFERENCES MenuCategories(CategoryID)
);

-- Create News table
CREATE TABLE News (
    NewsID INT PRIMARY KEY AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Content TEXT NOT NULL,
    Author VARCHAR(100),
    ImageURL VARCHAR(255),
    PublishDate DATE NOT NULL,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO RoomTypes (TypeName, Description, BasePrice, Capacity) VALUES
('Deluxe Suite', 'Well-Appropriated rooms for guests who desire a more luxurious experience', 399.00, 2),
('Family Suite', 'Multiple rooms and a common living area, for the family', 599.00, 4),
('Luxury Penthouse', 'Quality accomodations on the highest floor of the hotel', 799.00, 3);

INSERT INTO MenuCategories (CategoryName, Description) VALUES
('Breakfast', 'Morning delights to start your day'),
('Beverages', 'Refreshing drinks and hot beverages'),
('Main Course', 'Hearty meals for lunch and dinner');

INSERT INTO MenuItems (CategoryID, ItemName, Description, Price, ImageURL) VALUES
(1, 'Eggs & Bacon', 'Tasty Breakfast Combo', 15.99, 'Pictures/Food-1.jpg'),
(2, 'Coffee Or Tea', 'A classic choice for your daily dose of calm and comfort', 5.99, 'Pictures/Food-2.jpg'),
(1, 'Chia Oatmeal', 'A wholesome nutrient-packed breakfast delight', 12.99, 'Pictures/Food-3.jpg');
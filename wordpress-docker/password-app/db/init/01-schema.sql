CREATE TABLE common_passwords (
  password VARCHAR(128) NOT NULL PRIMARY KEY
);

-- Table name is the student ID per assignment requirement.
-- Only username + creation time are logged, never the password.
CREATE TABLE `2402294` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

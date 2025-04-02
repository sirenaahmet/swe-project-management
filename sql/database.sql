SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `Admin` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `Adoption` (
  `adoption_id` int(11) NOT NULL,
  `end_user_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `application_date` date NOT NULL DEFAULT curdate(),
  `status` varchar(20) NOT NULL DEFAULT 'Pending' CHECK (`status` in ('Pending','Approved','Rejected')),
  `notes` text DEFAULT NULL,
  `processed_by_staff` int(11) DEFAULT NULL,
  `agreement_pdf_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `Blog` (
  `blog_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `EndUser` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_info` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `Foster` (
  `foster_id` int(11) NOT NULL,
  `end_user_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Ongoing' CHECK (`status` in ('Ongoing','Completed')),
  `admin_notes` text DEFAULT NULL,
  `managed_by_staff` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `Pet` (
  `pet_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `species` varchar(50) NOT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Available' CHECK (`status` in ('Available','Fostered','Adopted','Not Available')),
  `is_featured` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `updated_by_staff` int(11) DEFAULT NULL,
  `photos` text DEFAULT NULL,
  `videos` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `Staff` (
  `staff_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `Admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `Adoption`
  ADD PRIMARY KEY (`adoption_id`),
  ADD KEY `end_user_id` (`end_user_id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `idx_adoption_status` (`status`),
  ADD KEY `processed_by_staff` (`processed_by_staff`);

ALTER TABLE `Blog`
  ADD PRIMARY KEY (`blog_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_blog_created_at` (`created_at`);

ALTER TABLE `EndUser`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `Foster`
  ADD PRIMARY KEY (`foster_id`),
  ADD KEY `end_user_id` (`end_user_id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `idx_foster_status` (`status`),
  ADD KEY `managed_by_staff` (`managed_by_staff`);

ALTER TABLE `Pet`
  ADD PRIMARY KEY (`pet_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_pet_status` (`status`),
  ADD KEY `idx_pet_species` (`species`),
  ADD KEY `idx_pet_breed` (`breed`),
  ADD KEY `updated_by_staff` (`updated_by_staff`);

ALTER TABLE `Staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `created_by` (`created_by`);

ALTER TABLE `Admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Adoption`
  MODIFY `adoption_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Blog`
  MODIFY `blog_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `EndUser`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Foster`
  MODIFY `foster_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Pet`
  MODIFY `pet_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `Adoption`
  ADD CONSTRAINT `adoption_ibfk_1` FOREIGN KEY (`end_user_id`) REFERENCES `EndUser` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adoption_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `Pet` (`pet_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adoption_ibfk_3` FOREIGN KEY (`processed_by_staff`) REFERENCES `Staff` (`staff_id`) ON DELETE SET NULL;

ALTER TABLE `Blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `Admin` (`admin_id`) ON DELETE SET NULL;

ALTER TABLE `Foster`
  ADD CONSTRAINT `foster_ibfk_1` FOREIGN KEY (`end_user_id`) REFERENCES `EndUser` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `foster_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `Pet` (`pet_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `foster_ibfk_3` FOREIGN KEY (`managed_by_staff`) REFERENCES `Staff` (`staff_id`) ON DELETE SET NULL;

ALTER TABLE `Pet`
  ADD CONSTRAINT `pet_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `Admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pet_ibfk_2` FOREIGN KEY (`updated_by_staff`) REFERENCES `Staff` (`staff_id`) ON DELETE SET NULL;

ALTER TABLE `Staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `Admin` (`admin_id`) ON DELETE SET NULL;
COMMIT;

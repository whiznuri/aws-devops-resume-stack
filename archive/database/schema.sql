SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `forum_replies`, `forum_topics`, `forum_categories`, `pets`, `breeds`, `pet_types`, `users`;
SET FOREIGN_KEY_CHECKS = 1;

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS `lovelypet_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `lovelypet_db`;

-- 1. Table: users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('member', 'staff', 'admin') DEFAULT 'member',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: pet_types
CREATE TABLE IF NOT EXISTS `pet_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `type_name` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: breeds (สารานุกรม 20 สายพันธุ์)
CREATE TABLE IF NOT EXISTS `breeds` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `category` ENUM('dog', 'cat', 'other') NOT NULL,
  `summary` VARCHAR(500) DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table: pets (ตารางสัตว์เลี้ยง)
CREATE TABLE IF NOT EXISTS `pets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `breed_id` INT DEFAULT NULL,
  `custom_breed` VARCHAR(100) DEFAULT NULL,
  `user_id` INT DEFAULT NULL,
  `age_years` INT DEFAULT 0,
  `gender` ENUM('male', 'female') DEFAULT 'male',
  `price` DECIMAL(10,2) DEFAULT 0.00,
  `like_count` INT DEFAULT 0,
  `status` ENUM('available', 'reserved', 'sold') DEFAULT 'available',
  `image_path` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`breed_id`) REFERENCES `breeds`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- SEED MOCK DATA
-- ====================================================================

-- Users
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role`) VALUES
(1, 'admin', 'bed4efa1d4fdbd954bd3705d6a2a78270ec9a52ecfbfb010c61862af5c76af1761ffeb1aef6aca1bf5d02b3781aa854fabd2b69c790de74e17ecfec3cb6ac4bf', 'System Administrator', 'admin@lovelypet.com', 'admin'),
(2, 'staff_01', 'bed4efa1d4fdbd954bd3705d6a2a78270ec9a52ecfbfb010c61862af5c76af1761ffeb1aef6aca1bf5d02b3781aa854fabd2b69c790de74e17ecfec3cb6ac4bf', 'Staff Officer', 'staff@lovelypet.com', 'staff'),
(3, 'diff_owner', 'bed4efa1d4fdbd954bd3705d6a2a78270ec9a52ecfbfb010c61862af5c76af1761ffeb1aef6aca1bf5d02b3781aa854fabd2b69c790de74e17ecfec3cb6ac4bf', 'Diff Natural', 'diff@lovelypet.com', 'member');

-- Pet Types
INSERT INTO `pet_types` (`id`, `type_name`) VALUES 
(1, 'สุนัข'), 
(2, 'แมว'), 
(3, 'สัตว์เลี้ยงอื่นๆ');

-- Breeds (สารานุกรม 20 สายพันธุ์)
INSERT INTO `breeds` (`id`, `name`, `category`, `summary`, `image_path`) VALUES
(1,  'Shiba Inu',          'dog',   'Alert, active, and bold Japanese breed with a fox-like appearance.', 'https://images.unsplash.com/photo-1641298929069-ec0b666fcb24?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(2,  'Golden Retriever',   'dog',   'Intelligent, friendly, and devoted family companion.',              'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(3,  'German Shepherd',    'dog',   'Intelligent, versatile, and highly loyal working dog.',             'https://images.unsplash.com/photo-1662939092439-67d3d9d733b4?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(4,  'French Bulldog',     'dog',   'Playful, adaptable, and smart companion with bat-like ears.',       'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(5,  'Poodle',             'dog',   'Exceptionally smart, active, and highly trainable breed.',          'https://images.unsplash.com/photo-1605244863941-3a3ed921c60d?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(6,  'Beagle',             'dog',   'Curious, friendly, and merry hound with an excellent nose.',        'https://images.unsplash.com/photo-1707298737261-069e2d529eaa?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(7,  'Corgi',              'dog',   'Affectionate, smart, and energetic short-legged herd dog.',         'https://images.unsplash.com/photo-1713575314497-2795fab05bb2?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(8,  'Maine Coon',         'cat',   'Gentle giant of the cat world, known for fluffy fur and size.',    'https://images.unsplash.com/photo-1606214174585-fe31582dc6ee?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(9,  'British Shorthair',  'cat',   'Easygoing, calm, and round-faced companion with a dense coat.',     'https://images.unsplash.com/photo-1584396888493-06386077e877?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(10, 'Persian',            'cat',   'Quiet, gentle, and sweet-tempered cat with long luxurious fur.',    'https://images.unsplash.com/flagged/photo-1557427705-d543f7da720f?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(11, 'Scottish Fold',      'cat',   'Loving, calm, and recognizable by its folded ear appearance.',      'https://images.unsplash.com/photo-1634206332775-3da1b8b3c73c?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(12, 'Siamese',            'cat',   'Vocal, affectionate, and striking cat with color-point coat.',     'https://images.unsplash.com/photo-1488740304459-45c4277e7daf?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(13, 'Sphynx',             'cat',   'Hairless, energetic, highly affectionate, and attention-loving.',   'https://images.unsplash.com/photo-1694718686061-5e201dcb76f9?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(14, 'Ragdoll',            'cat',   'Docile, placid, and affectionate cat that goes limp when held.',    'https://images.unsplash.com/photo-1749484071739-ae7a173b849f?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(15, 'Netherland Dwarf',   'other', 'One of the smallest rabbit breeds, curious and energetic.',         'https://images.unsplash.com/photo-1668791627301-23690de58952?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(16, 'Holland Lop',        'other', 'Popular dwarf rabbit known for its sweet posture and lop ears.',   'https://images.unsplash.com/photo-1650290145779-e05602773fc7?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(17, 'Cockatiel',          'other', 'Friendly small parrot known for its distinctive yellow crest.',     'https://images.unsplash.com/photo-1761627064452-68769313f360?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(18, 'Budgerigar',         'other', 'Small, colorful, and highly popular companion parakeet.',          'https://images.unsplash.com/photo-1648222473707-2c35edc61c27?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(19, 'Syrian Hamster',     'other', 'Solitary, friendly small rodent, great for nocturnal owners.',      'https://images.unsplash.com/photo-1676918555382-fcd06a483e25?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(20, 'Sugar Glider',       'other', 'Small, nocturnal gliding marsupial that forms strong bonds.',       'https://images.unsplash.com/photo-1627224285633-09faccb8a994?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80');

-- Pets (สัตว์เลี้ยง 8 ตัวสำหรับ Leaderboard)
INSERT INTO `pets` (`id`, `name`, `breed_id`, `user_id`, `age_years`, `gender`, `price`, `like_count`, `status`, `image_path`) VALUES
(1, 'Mochi',   1,  3, 2, 'male',   18000.00, 150, 'available', 'https://images.unsplash.com/photo-1605163289354-a8b102a71bcf?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(2, 'Luna',    8,  3, 1, 'female', 12000.00, 135, 'available', 'https://images.unsplash.com/photo-1593289099690-f51dc18966ff?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(3, 'Charlie', 4,  3, 2, 'male',   25000.00, 120, 'available', 'https://images.unsplash.com/photo-1723843095320-c5eedd1b1ab5?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(4, 'Coco',    15, 3, 1, 'male',    3500.00,  98, 'available', 'https://images.unsplash.com/photo-1605059378634-7337e66131fe?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(5, 'Melon',   9,  3, 2, 'female', 15000.00,  85, 'available', 'https://images.unsplash.com/photo-1759299710457-e80cb622494c?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(6, 'Toffee',  7,  3, 1, 'male',   20000.00,  72, 'available', 'https://images.unsplash.com/photo-1557973557-ddfa9ee8c5bf?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(7, 'Pudding', 17, 3, 1, 'female',  2500.00,  60, 'available', 'https://images.unsplash.com/photo-1517101724602-c257fe568157?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80'),
(8, 'Biscuit', 3,  3, 2, 'male',   22000.00,  45, 'reserved',  'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?auto=format&fit=crop&crop=entropy&w=600&h=400&q=80');

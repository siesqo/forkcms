CREATE TABLE IF NOT EXISTS `FaqCategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sequence` int(11) NOT NULL DEFAULT '0',
  `extraId` int(11) DEFAULT NULL,
  `createdOn` datetime NOT NULL,
  `editedOn` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `FaqCategoryTranslation` (
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoryId` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`locale`, `categoryId`),
  KEY `IDX_FaqCategoryTranslation_category` (`categoryId`),
  CONSTRAINT `FK_FaqCategoryTranslation_category` FOREIGN KEY (`categoryId`) REFERENCES `FaqCategory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `FaqQuestion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryId` int(11) DEFAULT NULL,
  `sequence` int(11) NOT NULL DEFAULT '0',
  `numViews` int(11) NOT NULL DEFAULT '0',
  `numUsefulYes` int(11) NOT NULL DEFAULT '0',
  `numUsefulNo` int(11) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `createdOn` datetime NOT NULL,
  `editedOn` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FaqQuestion_category` (`categoryId`),
  CONSTRAINT `FK_FaqQuestion_category` FOREIGN KEY (`categoryId`) REFERENCES `FaqCategory` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `FaqQuestionTranslation` (
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `questionId` int(11) NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`locale`, `questionId`),
  KEY `IDX_FaqQuestionTranslation_question` (`questionId`),
  CONSTRAINT `FK_FaqQuestionTranslation_question` FOREIGN KEY (`questionId`) REFERENCES `FaqQuestion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `FaqFeedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `questionId` int(11) NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT '0',
  `createdOn` datetime NOT NULL,
  `editedOn` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FaqFeedback_question` (`questionId`),
  CONSTRAINT `FK_FaqFeedback_question` FOREIGN KEY (`questionId`) REFERENCES `FaqQuestion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

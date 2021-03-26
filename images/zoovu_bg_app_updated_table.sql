

CREATE TABLE `247webhooks` (
  `id` int(11) NOT NULL,
  `email_id` varchar(255) NOT NULL,
  `webhook_bc_id` varchar(255) NOT NULL,
  `scope` varchar(255) NOT NULL,
  `destination` text NOT NULL,
  `api_response` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `webhook_log`
--

CREATE TABLE `webhook_log` (
  `id` int(11) NOT NULL,
  `email_id` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `operation` varchar(255) NOT NULL,
  `api_response` longtext NOT NULL,
  `cat_or_product_id` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `247webhooks`
--
ALTER TABLE `247webhooks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `webhook_log`
--
ALTER TABLE `webhook_log`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `247webhooks`
--
ALTER TABLE `247webhooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `webhook_log`
--
ALTER TABLE `webhook_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

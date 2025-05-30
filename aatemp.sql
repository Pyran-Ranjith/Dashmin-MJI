--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`)
MySQL said: Documentation

#1452 - Cannot add or update a child row: a foreign key constraint fails (`if0_37657216_2`.`#sql-alter-17875c-435f0`, CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`))
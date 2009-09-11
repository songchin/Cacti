ALTER TABLE host_snmp_cache ADD COLUMN present tinyint NOT NULL DEFAULT '1' AFTER `oid`, ADD INDEX present USING BTREE (present);
ALTER TABLE poller_item ADD COLUMN present tinyint NOT NULL DEFAULT '1' AFTER `action`, ADD INDEX present USING BTREE (present);
ALTER TABLE poller_reindex ADD COLUMN present tinyint NOT NULL DEFAULT '1' AFTER `action`, ADD INDEX present USING BTREE (present);
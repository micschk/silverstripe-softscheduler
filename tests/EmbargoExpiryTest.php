<?php

//class EmbargoExpiryTest extends SapphireTest {
//	static $fixture_file = 'embargoexpiry/tests/EmbargoExpiryTest.yml';
//	
//	function testEmbargo() {
//		Permission::flush_permission_cache();
//		$page = new SiteTree();
//		$page->Embargo = date('Y-m-d H:i:s', strtotime('+2 seconds'));
//		$decorator = $page->extInstance('EmbargoExpiryDecorator');
//		$decorator->setOwner($page);
//		$admin = $this->objFromFixture('Member', 'embargoexpiryadmin');
//		$nonAdmin = $this->objFromFixture('Member', 'embargoexpiryuser');
//		$viewer = $this->objFromFixture('Member', 'embargoexpiryviewer');
//		$this->assertFalse($decorator->canView($nonAdmin));
//		$this->assertTrue($decorator->canView($admin));
//		Permission::$admin_implies_all = false;
//		Permission::flush_permission_cache();
//		$this->assertFalse($decorator->canView($admin));
//		Permission::$admin_implies_all = true;
//		Permission::flush_permission_cache();
//		$page->Embargo = date('Y-m-d H:i:s', strtotime('-2 seconds'));
//		$this->assertTrue($decorator->canView($viewer));
//		$this->assertTrue($decorator->canView($nonAdmin));
//		$this->assertTrue($decorator->canView($admin));
//		Permission::$admin_implies_all = false;
//		Permission::flush_permission_cache();
//		$this->assertTrue($decorator->canView($admin));
//		Permission::$admin_implies_all = true;
//		Permission::flush_permission_cache();
//	}
//	
//	function testExpiry() {
//		Permission::flush_permission_cache();
//		$page = new SiteTree();
//		$page->Expiry = date('Y-m-d H:i:s', strtotime('+2 seconds'));
//		$decorator = $page->extInstance('EmbargoExpiryDecorator');
//		$decorator->setOwner($page);
//		$admin = $this->objFromFixture('Member', 'embargoexpiryadmin');
//		$nonAdmin = $this->objFromFixture('Member', 'embargoexpiryuser');
//		$viewer = $this->objFromFixture('Member', 'embargoexpiryviewer');
//		$this->assertTrue($decorator->canView($nonAdmin));
//		$this->assertTrue($decorator->canView($admin));
//		$this->assertTrue($decorator->canView($viewer));
//		Permission::$admin_implies_all = false;
//		Permission::flush_permission_cache();
//		$this->assertTrue($decorator->canView($admin));
//		Permission::$admin_implies_all = true;
//		Permission::flush_permission_cache();
//		$page->Expiry = date('Y-m-d H:i:s', strtotime('-2 seconds'));
//		$this->assertFalse($decorator->canView($nonAdmin));
//		$this->assertTrue($decorator->canView($admin));
//		$this->assertTrue($decorator->canView($viewer));
//		Permission::$admin_implies_all = false;
//		Permission::flush_permission_cache();
//		$this->assertFalse($decorator->canView($admin));
//		Permission::$admin_implies_all = true;
//		Permission::flush_permission_cache();
//	}
//}

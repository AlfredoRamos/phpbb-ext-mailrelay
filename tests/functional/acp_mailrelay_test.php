<?php

/**
 * Mailrelay extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@yandex.com>
 * @copyright 2020 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\mailrelay\tests\functional;

/**
 * @group functional
 */
class acp_mailrelay_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return ['alfredoramos/mailrelay'];
	}

	public function setUp(): void
	{
		parent::setUp();
		$this->add_lang_ext('alfredoramos/mailrelay', [
			'acp/info_acp_common',
			'acp/settings'
		]);
		$this->login();
		$this->admin_login();
	}

	public function test_acp_form_settings()
	{
		$crawler = self::request('GET', sprintf(
			'adm/index.php?i=-alfredoramos-mailrelay-acp-main_module&mode=settings&sid=%s',
			$this->sid
		));

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();

		$this->assertSame(1, $crawler->filter('#mailrelay-settings')->count());

		$this->assertTrue($form->has('mailrelay_hostname'));
		$this->assertSame('', $form->get('mailrelay_hostname')->getValue());

		$this->assertTrue($form->has('mailrelay_domain'));
		$this->assertSame('ipzmarketing.com', $form->get('mailrelay_domain')->getValue());

		$this->assertTrue($form->has('mailrelay_api_key'));
		$this->assertSame('', $form->get('mailrelay_api_key')->getValue());

		$this->assertTrue($form->has('mailrelay_group_id'));
		$this->assertSame('1', $form->get('mailrelay_group_id')->getValue());

		$this->assertTrue($form->has('mailrelay_sync_packet_size'));
		$this->assertSame('150', $form->get('mailrelay_sync_packet_size')->getValue());

		$this->assertTrue($form->has('mailrelay_sync_frequency_number'));
		$this->assertSame('1', $form->get('mailrelay_sync_frequency_number')->getValue());

		$this->assertTrue($form->has('mailrelay_sync_frequency_number'));
		$this->assertSame('hour', $form->get('mailrelay_sync_frequency_type')->getValue());
	}
}

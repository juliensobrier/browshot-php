<?php

require_once __DIR__ . '/TestCase.php';

class ArtifactLiveAPI extends TestCase
{
	public static function artifactFormats(): array
	{
		return array(
			'PDF' => array('PDF', 'screenshot_pdf', '%PDF-', 0),
			'WebM' => array('WEBM', 'screenshot_video', "\x1a\x45\xdf\xa3", 0),
			'MP4' => array('MP4', 'screenshot_video', 'ftyp', 4),
			'GIF' => array('GIF', 'screenshot_video', 'GIF8', 0)
		);
	}

	/**
	 * @dataProvider artifactFormats
	 */
	public function testDownloadsAndSavesLiveArtifact($format, $method, $signature, $offset)
	{
		$id = $this->fixtureId('BROWSHOT_' . $format . '_ID');
		$kind = $format === 'PDF' ? 'pdf' : 'video';
		$info = $this->browshot->screenshot_info($id, array('details' => 1));
		$this->assertSame('finished', $info->status);
		$this->assertEquals(1, $info->$kind);
		$this->assertNotEmpty($info->{$kind . '_url'});

		$content = $this->browshot->$method($id);
		$this->assertSame($signature, substr($content, $offset, strlen($signature)));

		$file = tempnam(sys_get_temp_dir(), 'browshot-');
		try {
			$save = $method . '_file';
			$this->assertSame($file, $this->browshot->$save($id, $file));
			$this->assertSame($content, file_get_contents($file));
		} finally {
			unlink($file);
		}
	}

	public function testListsNativeCountryInstancesAtOneCredit()
	{
		$catalog = $this->browshot->instance_list();
		$countries = array('France', 'India', 'Finland', 'United Kingdom');
		$instances = array();
		foreach ($catalog->shared as $instance) {
			$instances[$instance->id] = $instance;
		}
		for ($id = 373; $id <= 388; $id++) {
			$this->assertArrayHasKey($id, $instances);
			$this->assertSame($countries[intdiv($id - 373, 4)], $instances[$id]->country);
			$this->assertEquals(1, $instances[$id]->screenshot_cost);
		}
	}

	public function testReturnsEmptyForUnavailableOrUnauthorizedArtifacts()
	{
		$invalid = new Browshot('invalid-test-key');
		foreach (array('screenshot_pdf', 'screenshot_video') as $method) {
			$this->assertSame('', $this->browshot->$method(1));
			$this->assertSame('', $invalid->$method(1));
		}
	}
}

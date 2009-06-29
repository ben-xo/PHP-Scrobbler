<?php
/**
 * PHP Scrobbler
 *
 * This class lets you submit tracks to a lastfm account. Curl needed.
 *
 * Basic usage:
 *
 * <?php
 * require('Scrobbler.php');
 * $scrobbler = new Scrobbler('lastfmUser', 'password');
 * $scrobbler->add('Jerry Goldsmith', 'The space jockey', 'alien', 289);
 * $scrobbler->submit();
 * ?>
 *
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link www.desfrenes.com
 */
class Scrobbler_Exception extends Exception{}
class Scrobbler
{
	// change this according to your client id and version
	const clientId      = 'nuc';
	const clientVer     = '1';

	const SCROBBLER_URL = 'http://post.audioscrobbler.com/?hs=true&p=1.2.1&c=<client-id>&v=<client-ver>&u=<user>&t=<timestamp>&a=<auth>';
	// curl timeout
	const TIMEOUT       = 10;

	// lastfm user
	private $user;
	// lastfm user password
	private $password;
	// scrobbler session id
	private $sessionId         = '';
	// last handshake failure timestamp (0 = no failure)
	private $handShakeFailure  = 0;
	// number of hard failures
	private $submitFailures    = 0;
	// store tracks here
	private $queue             = array();
	private $nowPlayingUrl;
	private $submissionUrl;

	/**
	 * New Scrobbler
	 *
	 * @param string LastFM login
	 * @param string LastFM password
	 */
	public function __construct($user, $password)
	{
		$this->user     = $user;
		$this->password = $password;
	}

	/**
	 * Add a track to the queue
	 *
	 * @param string artist name
	 * @param string track title
	 * @param string album title
	 * @param integer track length (seconds)
	 * @param integer track play timestamp
	 * @param integer track number
	 * @param string source type (see lastFM API docs)
	 * @param integer rating
	 * @param string music brain track ID
	 * @return boolean
	 */
	public function add($artist, $track, $album = '', $trackDuration = '', $scrobbleTime = '', $trackNumber = '', $source = 'P', $rating = '', $mbTrackId = '')
	{
		if(empty($scrobbleTime))
		{
			$scrobbleTime = time();
		}
		$this->queue[] = array('artist'        => $artist,
							   'track'         => $track,
							   'scrobbleTime'  => $scrobbleTime,
							   'trackDuration' => $trackDuration,
							   'album'         => $album,
							   'trackNumber'   => $trackNumber,
							   'source'        => $source,
							   'rating'        => $rating,
							   'mbTrackId'     => $mbTrackId
							   );
		return true;
	}

	/**
	 * Submission process
	 *
	 * @throws Scrobbler_Exception
	 * @return boolean
	 */
	public function submit()
	{
		if(empty($this->queue))
		{
			throw new Scrobbler_Exception('Nothing to submit.');
			return false;
		}
		if(empty($this->sessionId) or $this->submitFailures > 2)
		{
			$this->handShake();
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_TIMEOUT, self::TIMEOUT);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_URL, $this->submissionUrl);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->generatePostData());
		$data = curl_exec($curl);
		curl_close ($curl);

		$data = explode("\n", $data);
		//error_log($this->generatePostData());
		//error_log($data[0]);
		if($data[0] != 'OK')
		{
			$this->submitFailures++;
			if($data[0] == 'BADSESSION')
			{
				throw new Scrobbler_Exception('Bad session id.');
			}
			else
			{
				throw new Scrobbler_Exception('Submission failed : ' . $data[0]);
			}
			return false;
		}
		else
		{
			$this->queue = array();
			return true;
		}
		return false;
	}

	private function handShake()
	{
		if(empty($this->user) or empty($this->password))
		{
			throw new Scrobbler_Exception('Authentification credentials missing.');
			return false;
		}
		$curl = curl_init($this->generateScrobblerUrl());
		curl_setopt($curl, CURLOPT_TIMEOUT, self::TIMEOUT);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

		$data = curl_exec($curl);

		curl_close($curl);
		$data = explode("\n", $data);
		if($data[0] != 'OK')
		{
			$this->handShakeFailure = time();
			switch($data[0])
			{
				case 'BANNED':
					throw new Scrobbler_Exception('Client banned.');
					break;
				case 'BADTIME':
					throw new Scrobbler_Exception('Wrong system clock.');
					break;
				case 'BADAUTH':
					throw new Scrobbler_Exception('Wrong credentials.');
					break;
				default:
					throw new Scrobbler_Exception('Unexpected handshake error: ' . $data[0]);
					break;
			}
			return false;
		}
		else
		{
			$this->sessionId        = trim($data[1]);
			$this->nowPlayingUrl    = trim($data[2]);
			$this->submissionUrl    = trim($data[3]);
			$this->handShakeFailure = 0;
			$this->submitFailures   = 0;
			return true;
		}

		return false;
	}

	private function generateScrobblerUrl()
	{
		$stamp = time();
		return str_replace(
						   array('<client-id>',
								 '<client-ver>',
								 '<user>',
								 '<timestamp>',
								 '<auth>'),
						   array(self::clientId,
								 self::clientVer,
								 $this->user,
								 $stamp,
								 md5(md5($this->password) . $stamp)),
						   self::SCROBBLER_URL
						   );
	}

	private function generatePostData()
	{
		$body = 's=' . $this->sessionId . '&';
		$i = 0;
		foreach($this->queue as $item)
		{
			$body .= 'a[' . $i . ']=' . urlencode($item['artist']) . '&'
			. 't[' . $i . ']=' . urlencode($item['track']) . '&'
			. 'i[' . $i . ']=' . $item['scrobbleTime'] . '&'
			. 'o[' . $i . ']=' . $item['source'] . '&'
			. 'r[' . $i . ']=' . $item['rating'] . '&'
			. 'l[' . $i . ']=' . $item['trackDuration'] . '&'
			. 'b[' . $i . ']=' . urlencode($item['album']) . '&'
			. 'n[' . $i . ']=' . $item['trackNumber'] . '&'
			. 'm[' . $i . ']=' . $item['mbTrackId'] . '&';
			$i++;
		}

		return $body;
	}
}

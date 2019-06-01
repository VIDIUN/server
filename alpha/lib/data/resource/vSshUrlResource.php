<?php
/**
 * Used to ingest media that is available on remote SSH server and accessible using the supplied URL, media file will be downloaded using import job in order to make the asset ready.
 *
 * @package Core
 * @subpackage model.data
 */
class vSshUrlResource extends vUrlResource 
{
	/**
	 * SSH private key
	 * @var string
	 */
	private $privateKey;
	
	/**
	 * SSH public key
	 * @var string
	 */
	private $publicKey;
	
	/**
	 * Passphrase for SSH keys
	 * @var string
	 */
	private $keyPassphrase;
	
	public function getType()
	{
		return 'vUrlResource';
	}
	
	/**
     * @return the $privateKey
     */
    public function getPrivateKey ()
    {
        return $this->privateKey;
    }

	/**
     * @param field_type $privateKey
     */
    public function setPrivateKey ($privateKey)
    {
        $this->privateKey = $privateKey;
    }

	/**
     * @return the $publicKey
     */
    public function getPublicKey ()
    {
        return $this->publicKey;
    }

	/**
     * @param field_type $publicKey
     */
    public function setPublicKey ($publicKey)
    {
        $this->publicKey = $publicKey;
    }

	/**
     * @return the $keyPassphrase
     */
    public function getKeyPassphrase ()
    {
        return $this->keyPassphrase;
    }

	/**
     * @param field_type $keyPassphrase
     */
    public function setKeyPassphrase ($keyPassphrase)
    {
        $this->keyPassphrase = $keyPassphrase;
    }
    
    /**
     * (non-PHPdoc)
     * @see vUrlResource::getImportJobData()
     */
	public function getImportJobData()
	{
	    $sshImportJobData = new vSshImportJobData();
	    $sshImportJobData->setSrcFileUrl($this->getUrl());
	    $sshImportJobData->setPrivateKey($this->getPrivateKey());
	    $sshImportJobData->setPublicKey($this->getPublicKey());
	    $sshImportJobData->setPassPhrase($this->getKeyPassphrase());
	    return $sshImportJobData;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see vUrlResource::forceAsyncDownload()
	 */
	public function getForceAsyncDownload()
	{
	    return true;
	}
    
}
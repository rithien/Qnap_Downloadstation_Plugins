<?php
require_once('ddosguard.php');
class dbree implements ISite,  IDownload {
    private $url;
    /*
     * dbree()
     * @param {string} $url
     * @param {string} $username
     * @param {string} $password
     * @param {string} $meta
     */
    public function __construct($url = null, $username = null, $password = null, $meta = null) {
        $this->url = $url;
    }
    
    
    /*
     * GetDownloadLink()
     * @return {mixed} DownloadLink object or DownloadLink array
     */
    public function GetDownloadLink() {
        $c = new ddosguard(true, "cookie.txt");
        $response = $c->get_download($this->url);
        $myurl="https:".$response["location"];
        preg_match("/filename=\"(.+?)\"/", $response["content-disposition"], $filename);
        $path_info = mb_pathinfo($filename[1]);
        $dlink = new DownloadLink();
        $dlink->url = $myurl;
        $dlink->filename = $path_info['basename'];
        $dlink->base_name = $path_info['filename'];
        $dlink->ext_name = ".".$path_info['extension'];
        $dlink->filesize = (int) $response["content-length"];
        $dlink->refresh_time = 3600;

        return $dlink;
    }
    
    /*
     * RefreshDownloadLink()
     * @param {DownloadLink} $dlink
     * @return {DownloadLink} DownloadLink object
     */
    public function RefreshDownloadLink($dlink) {
        return $dlink;
    }
    
}
?>
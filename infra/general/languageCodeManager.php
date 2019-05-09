<?php

class languageCodeManager
{
    private static $arrayISO639 = null;
    private static $arrayISO639_T = null;
    private static $arrayISO639_B = null;
    private static $arrayVidiunName = null;

    const ISO639 = 0; //lowercase
    const ISO639_T = 1;
    const ISO639_B = 2;
    const ISO_NAME = 3;
    const ISO_NATIVE_NAME = 4;
    const VIDIUN_NAME = 5;


    public static function loadLanguageCodeMap()
    {
        $cacheFileName = vConf::get("cache_root_path") . "/infra/languageCodeMapCache.php";
        if(self::isAlreadyLoaded())
            return;
        else
        {
            $max_include_retries = 10;
            $cacheFileCode = null;
            while ((!@include_once($cacheFileName)) and $max_include_retries--) {
                if (!$cacheFileCode) {
                    $cacheFileCode = self::generateCacheFile();
                    if (!$cacheFileCode)
                        return;
                }

                $cacheDir = dirname($cacheFileName);
                if (!is_dir($cacheDir)) {
                    @mkdir($cacheDir);
                    chmod($cacheDir, 0775);
                }
                vFile::safeFilePutContents($cacheFileName, $cacheFileCode, 0644);
            }
            if (!file_exists($cacheFileName)) {
                throw new Exception("Could not include cached code file - {$cacheFileName}");
            }
        }
    }

    private static function isAlreadyLoaded()
    {
        return isset(self::$arrayISO639) && isset(self::$arrayISO639_T) &&  isset(self::$arrayISO639_B) &&isset(self::$arrayVidiunName);
    }

    public static function getObjectFromTwoCode($codeUppercase)
    {
        if(!self::isAlreadyLoaded())
            self::loadLanguageCodeMap();
        return isset(self::$arrayISO639[$codeUppercase]) ? self::$arrayISO639[$codeUppercase] : null;
    }

    public static function getObjectFromThreeCode($codeT)
    {
        if(!self::isAlreadyLoaded())
            self::loadLanguageCodeMap();
        $val = isset(self::$arrayISO639_T[$codeT]) ? self::$arrayISO639_T[$codeT] : null;
	    if (!$val)
            $val = isset(self::$arrayISO639_B[$codeT]) ? self::$arrayISO639_B[$codeT] : null;
        return self::getObjectFromTwoCode($val);
    }

    public static function getFullLanguageNameFromThreeCode($codeT)
    {
        if(!self::isAlreadyLoaded())
            self::loadLanguageCodeMap();
        $languageObj = self::getObjectFromThreeCode($codeT);
        return !is_null($languageObj) ? $languageObj[self::VIDIUN_NAME] : $codeT;
    }

    public static function getObjectFromVidiunName($vidiunName)
    {
        if(!self::isAlreadyLoaded())
            self::loadLanguageCodeMap();
        $val = isset(self::$arrayVidiunName[$vidiunName]) ? self::$arrayVidiunName[$vidiunName] : null;
        return self::getObjectFromTwoCode($val);
    }

    public static function getTwoCodeFromVidiunName($vidiunName)
    {
        if(!self::isAlreadyLoaded())
            self::loadLanguageCodeMap();
        return isset(self::$arrayVidiunName[$vidiunName]) ? self::$arrayVidiunName[$vidiunName] : null;
    }

	public static function getTwoCodeLowerFromThreeCode($code)
	{
		if(!self::isAlreadyLoaded())
			self::loadLanguageCodeMap();
		$obj = self::getObjectFromThreeCode($code);
		return !is_null($obj) ? $obj[self::ISO639] : null;
	}

    /**
     * @param $language - the language to search
     * @return the 2 code key or $defaultCode if not known
     */
    public static function getLanguageKey($language,$langaugeKey = null)
    {
        if(!self::isAlreadyLoaded())
            self::loadLanguageCodeMap();

        if(isset(self::$arrayISO639[$language]))
            return $language;

        if(isset(self::$arrayISO639_T[$language]))
            return self::$arrayISO639_T[$language];

        if(isset(self::$arrayISO639_B[$language]))
            return self::$arrayISO639_B[$language];

        if(isset(self::$arrayVidiunName[$language]))
            return self::$arrayVidiunName[$language];

        else return $langaugeKey;
    }

    /**
     * @param $arrayISO639
     * @param $arrayISO639_T
     * @param $arrayISO639_B
     * @param $arrayVidiunName
     * @param $ISO639Upper - upper case language code as in ISO 639-1
     * @param $ISO639_1Lower - lower case language code as in ISO 639-1
     * @param $ISO639_T - lower case three letters language code as in ISO 639-2/T  - if two code is not official then the 3 code is made up
     * @param $ISO639_B - lower case three letters language code as in ISO 639-2/B  - if two code is not official then the 3 code is made up
     * @param $languageName - language name
     * @param $nativeName - native language name
     * @param $vidiunName - vidiun language name as in VidiunLanguage, if the language is not defined in vidiun then $vidiunName is the same as $languageName
     */
    private static function addLanguageToArrays(&$arrayISO639 , &$arrayISO639_T , &$arrayISO639_B, &$arrayVidiunName,
                                                $ISO639Upper ,$ISO639Lower,$ISO639_T,$ISO639_B,$languageName,$nativeName,$vidiunName=null)
    {
        if(is_null($vidiunName))
            $vidiunName = $languageName;
        if(is_null($ISO639_T))
            $ISO639_T = $ISO639_B;
        $arrayISO639[$ISO639Upper] = array($ISO639Lower,$ISO639_T,$ISO639_B,$languageName,$nativeName,$vidiunName);
        $arrayISO639_T[$ISO639_T] = $ISO639Upper;
        $arrayISO639_B[$ISO639_B] = $ISO639Upper;
        $arrayVidiunName[$vidiunName] = $ISO639Upper;
    }



    private static function generateCacheFile()
    {
        $tmpArrKeyCode = array(); //$arrayISO639
        $tmpArrThreeCodeT = array(); //$arrayISO639_T
        $tmpArrThreeCodeB = array(); //$arrayISO639_B
        $tmpArrVidiunName = array(); //$arrayVidiunName

        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AB', "ab", "abk", "abk", "Abkhaz", "\xd0\xb0\xd2\xa7\xd1\x81\xd1\x83\xd0\xb0 \xd0\xb1\xd1\x8b\xd0\xb7\xd1\x88\xd3\x99\xd0\xb0, \xd0\xb0\xd2\xa7\xd1\x81\xd1\x88\xd3\x99\xd0\xb0",'Abkhazian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AA', "aa", "aar", "aar", "Afar", "Afaraf",'Afar');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AF', "af", "afr", "afr", "Afrikaans", "Afrikaans",'Afrikaans');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AK', "ak", "aka", "aka", "Akan", "Akan",'Akan');  //not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SQ', "sq", "sqi", "alb", "Albanian", "Shqip",'Albanian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AM', "am", "amh", "amh", "Amharic", "\xe1\x8a\xa0\xe1\x88\x9b\xe1\x88\xad\xe1\x8a\x9b",'Amharic');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AR', "ar", "ara", "ara", "Arabic", "\xd8\xa7\xd9\x84\xd8\xb9\xd8\xb1\xd8\xa8\xd9\x8a\xd8\xa9",'Arabic');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AN', "an", "arg", "arg", "Aragonese", "aragon\xc3\xa9s",'Aragonese');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'HY', "hy", "hye", "arm", "Armenian", "\xd5\x80\xd5\xa1\xd5\xb5\xd5\xa5\xd6\x80\xd5\xa5\xd5\xb6",'Armenian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AS_', "as", "asm", "asm", "Assamese", "\xe0\xa6\x85\xe0\xa6\xb8\xe0\xa6\xae\xe0\xa7\x80\xe0\xa6\xaf\xe0\xa6\xbc\xe0\xa6\xbe",'Assamese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AV', "av", "ava", "ava", "Avaric", "\xd0\xb0\xd0\xb2\xd0\xb0\xd1\x80 \xd0\xbc\xd0\xb0\xd1\x86\xd3\x80, \xd0\xbc\xd0\xb0\xd0\xb3\xd3\x80\xd0\xb0\xd1\x80\xd1\x83\xd0\xbb \xd0\xbc\xd0\xb0\xd1\x86\xd3\x80",'Avaric');//not  yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AE', "ae", "ave", "ave", "Avestan", "avesta",'Avestan');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AY', "ay", "aym", "aym", "Aymara", "aymar aru",'Aymara');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'AZ', "az", "aze", "aze", "Azerbaijani", "az\xc9\x99rbaycan dili",'Azerbaijani');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'BM', "bm", "bam", "bam", "Bambara", "bamanankan",'Bambara'); // not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'BA', "ba", "bak", "bak", "Bashkir", "\xd0\xb1\xd0\xb0\xd1\x88\xd2\xa1\xd0\xbe\xd1\x80\xd1\x82 \xd1\x82\xd0\xb5\xd0\xbb\xd0\xb5",'Bashkir');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'EU', "eu", "eus", "baq", "Basque", "euskara, euskera",'Basque');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'BE', "be", "bel", "bel", "Belarusian", "\xd0\xb1\xd0\xb5\xd0\xbb\xd0\xb0\xd1\x80\xd1\x83\xd1\x81\xd0\xba\xd0\xb0\xd1\x8f \xd0\xbc\xd0\xbe\xd0\xb2\xd0\xb0",'Byelorussian (Belarusian)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'BN', "bn", "ben", "ben", "Bengali, Bangla", "\xe0\xa6\xac\xe0\xa6\xbe\xe0\xa6\x82\xe0\xa6\xb2\xe0\xa6\xbe",'Bengali (Bangla)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'BH', "bh", "bih", "bih", "Bihari", "\xe0\xa4\xad\xe0\xa5\x8b\xe0\xa4\x9c\xe0\xa4\xaa\xe0\xa5\x81\xe0\xa4\xb0\xe0\xa5\x80",'Bihari');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'BI', "bi", "bis", "bis", "Bislama", "Bislama",'Bislama');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'BS', "bs", "bos", "bos", "Bosnian", "bosanski jezik",'Bosnian');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'BR', "br", "bre", "bre", "Breton", "brezhoneg",'Breton');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'BG', "bg", "bul", "bul", "Bulgarian", "\xd0\xb1\xd1\x8a\xd0\xbb\xd0\xb3\xd0\xb0\xd1\x80\xd1\x81\xd0\xba\xd0\xb8 \xd0\xb5\xd0\xb7\xd0\xb8\xd0\xba",'Bulgarian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MY', "my", "mya", "bur", "Burmese", "\xe1\x80\x97\xe1\x80\x99\xe1\x80\xac\xe1\x80\x85\xe1\x80\xac",'Burmese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'CA', "ca", "cat", "cat", "Catalan", "catal\xc3\xa0",'Catalan');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'CH', "ch", "cha", "cha", "Chamorro", "Chamoru",'Chamorro');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'CE', "ce", "che", "che", "Chechen", "\xd0\xbd\xd0\xbe\xd1\x85\xd1\x87\xd0\xb8\xd0\xb9\xd0\xbd \xd0\xbc\xd0\xbe\xd1\x82\xd1\x82",'Chechen');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'NY', "ny", "nya", "nya", "Chichewa, Chewa, Nyanja", "chiChe\xc5\xb5",'Chichewa'); //not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'ZH', "zh", "zho", "chi", "Chinese", "\xe4\xb8\xad\xe6\x96\x87 (Zh\xc5\x8dngw\xc3\xa9n), \xe6\xb1\x89\xe8\xaf\xad, \xe6\xbc\xa2\xe8\xaa\x9e",'Chinese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'CV', "cv", "chv", "chv", "Chuvash", "\xd1\x87\xd3\x91\xd0\xb2\xd0\xb0\xd1\x88 \xd1\x87\xd3\x97\xd0\xbb\xd1\x85\xd0\xb8",'Chuvash');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KW', "kw", "cor", "cor", "Cornish", "Kernewek",'Cornish');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'CO', "co", "cos", "cos", "Corsican", "corsu, lingua corsa",'Corsican');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'CR', "cr", "cre", "cre", "Cree", "\xe1\x93\x80\xe1\x90\xa6\xe1\x90\x83\xe1\x94\xad\xe1\x90\x8d\xe1\x90\x8f\xe1\x90\xa3",'Cree');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'HR', "hr", "hrv", "hrv", "Croatian", "hrvatski jezik",'Croatian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'CS', "cs", "ces", "cze", "Czech", "\xc4\x8d" ,'Czech');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'DA', "da", "dan", "dan", "Danish", "dansk",'Danish');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'DV', "dv", "div", "div", "Divehi, Dhivehi, Maldivian", "\xde\x8b\xde\xa8\xde\x88\xde\xac\xde\x80\xde\xa8",'Divehi, Dhivehi, Maldivian');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'NL', "nl", "nld", "dut", "Dutch", "Nederlands, Vlaams",'Dutch');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'DZ', "dz", "dzo", "dzo", "Dzongkha", "\xe0\xbd\xa2\xe0\xbe\xab\xe0\xbd\xbc\xe0\xbd\x84\xe0\xbc\x8b\xe0\xbd\x81",'Bhutani');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'EN', "en", "eng", "eng", "English", "English",'English');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'EO', "eo", "epo", "epo", "Esperanto", "Esperanto",'Esperanto');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'ET', "et", "est", "est", "Estonian", "eesti, eesti keel",'Estonian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'EE', "ee", "ewe", "ewe", "Ewe", "E\xca\x8b",'Ewe'); //not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'FO', "fo", "fao", "fao", "Faroese", "f\xc3\xb8royskt",'Faeroese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'FJ', "fj", "fij", "fij", "Fijian", "vosa Vakaviti",'Fiji');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'FI', "fi", "fin", "fin", "Finnish", "suomi, suomen kieli",'Finnish');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'FR', "fr", "fra", "fre", "French", "fran\xc3\xa7",'French');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'FF', "ff", "ful", "ful", "Fula, Fulah, Pulaar, Pular", "Fulfulde, Pulaar, Pular",'Fula, Fulah, Pulaar, Pular');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'GL', "gl", "glg", "glg", "Galician", "galego",'Galician');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KA', "ka", "kat", "geo", "Georgian", "\xe1\x83\xa5\xe1\x83\x90\xe1\x83\xa0\xe1\x83\x97\xe1\x83\xa3\xe1\x83\x9a\xe1\x83\x98",'Georgian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'DE', "de", "deu", "ger", "German", "Deutsch",'German');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'EL', "el", "ell", "gre", "Greek (modern)", "\xce\xb5\xce\xbb\xce\xbb\xce\xb7\xce\xbd\xce\xb9\xce\xba\xce\xac",'Greek');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'GN', "gn", "grn", "grn", "Guaran\xc3\xad", "Ava\xc3\xb1",'Guarani');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'GU', "gu", "guj", "guj", "Gujarati", "\xe0\xaa\x97\xe0\xab\x81\xe0\xaa\x9c\xe0\xaa\xb0\xe0\xaa\xbe\xe0\xaa\xa4\xe0\xab\x80",'Gujarati');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'HT', "ht", "hat", "hat", "Haitian, Haitian Creole", "Krey\xc3\xb2l ayisyen",'Haitian, Haitian Creole');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'HA', "ha", "hau", "hau", "Hausa", "(Hausa) \xd9\x87\xd9\x8e\xd9\x88\xd9\x8f\xd8\xb3\xd9\x8e",'Hausa');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'HE', "he", "heb", "heb", "Hebrew (modern)", "\xd7\xa2\xd7\x91\xd7\xa8\xd7\x99\xd7\xaa",'Hebrew');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'HZ', "hz", "her", "her", "Herero", "Otjiherero",'Herero');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'HI', "hi", "hin", "hin", "Hindi", "\xe0\xa4\xb9\xe0\xa4\xbf\xe0\xa4\xa8\xe0\xa5\x8d\xe0\xa4\xa6\xe0\xa5\x80, \xe0\xa4\xb9\xe0\xa4\xbf\xe0\xa4\x82\xe0\xa4\xa6\xe0\xa5\x80",'Hindi');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'HO', "ho", "hmo", "hmo", "Hiri Motu", "Hiri Motu",'Hiri Motu');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'HU', "hu", "hun", "hun", "Hungarian", "magyar",'Hungarian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'IA', "ia", "ina", "ina", "Interlingua", "Interlingua",'Interlingua');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'ID', "id", "ind", "ind", "Indonesian", "Bahasa Indonesia",'Indonesian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'IE', "ie", "ile", "ile", "Interlingue", "Originally called Occidental; then Interlingue after WWII",'Interlingue');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'GA', "ga", "gle", "gle", "Irish", "Gaeilge",'Irish');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'IG', "ig", "ibo", "ibo", "Igbo", "As\xe1\xbb\xa5s\xe1\xbb\xa5 Igbo",'Igbo');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'IK', "ik", "ipk", "ipk", "Inupiaq", "I\xc3\xb1upiaq, I\xc3\xb1upiatun",'Inupiak');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'IO', "io", "ido", "ido", "Ido", "Ido",'Ido');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'IS', "is", "isl", "ice", "Icelandic", "\xc3\x8dslenska",'Icelandic');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'IT', "it", "ita", "ita", "Italian", "italiano",'Italian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'IU', "iu", "iku", "iku", "Inuktitut", "\xe1\x90\x83\xe1\x93\x84\xe1\x92\x83\xe1\x91\x8e\xe1\x91\x90\xe1\x91\xa6",'Inuktitut');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'JA', "ja", "jpn", "jpn", "Japanese", "\xe6\x97\xa5\xe6\x9c\xac\xe8\xaa\x9e (\xe3\x81\xab\xe3\x81\xbb\xe3\x82\x93\xe3\x81\x94)",'Japanese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'JV', "jv", "jav", "jav", "Javanese", "\xea\xa6\xa7\xea\xa6\xb1\xea\xa6\x97\xea\xa6\xae",'Javanese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KL', "kl", "kal", "kal", "Kalaallisut, Greenlandic", "kalaallisut, kalaallit oqaasii",'Greenlandic');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KN', "kn", "kan", "kan", "Kannada", "\xe0\xb2\x95\xe0\xb2\xa8\xe0\xb3\x8d\xe0\xb2\xa8\xe0\xb2\xa1",'Kannada');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KR', "kr", "kau", "kau", "Kanuri", "Kanuri",'Kanuri');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KS', "ks", "kas", "kas", "Kashmiri", "\xe0\xa4\x95\xe0\xa4\xb6\xe0\xa5\x8d\xe0\xa4\xae\xe0\xa5\x80\xe0\xa4\xb0\xe0\xa5\x80, \xd9\x83\xd8\xb4\xd9\x85\xd9\x8a\xd8\xb1\xd9\x8a\xe2\x80\x8e",'Kashmiri');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KK', "kk", "kaz", "kaz", "Kazakh", "\xd2\x9b\xd0\xb0\xd0\xb7\xd0\xb0\xd2\x9b \xd1\x82\xd1\x96\xd0\xbb\xd1\x96",'Kazakh');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KM', "km", "khm", "khm", "Khmer", "\xe1\x9e\x81\xe1\x9f\x92\xe1\x9e\x98\xe1\x9f\x82\xe1\x9e\x9a, \xe1\x9e\x81\xe1\x9f\x81\xe1\x9e\x98\xe1\x9e\x9a\xe1\x9e\x97\xe1\x9e\xb6\xe1\x9e\x9f\xe1\x9e\xb6, \xe1\x9e\x97\xe1\x9e\xb6\xe1\x9e\x9f\xe1\x9e\xb6\xe1\x9e\x81\xe1\x9f\x92\xe1\x9e\x98\xe1\x9f\x82\xe1\x9e\x9a",'Cambodian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KI', "ki", "kik", "kik", "Kikuyu, Gikuyu", "G\xc4\xa9k\xc5\xa9y\xc5\xa9",'Kikuyu, Gikuyu');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'RW', "rw", "kin", "kin", "Kinyarwanda", "Ikinyarwanda",'Kinyarwanda (Ruanda)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KY', "ky", "kir", "kir", "Kyrgyz", "\xd0\x9a\xd1\x8b\xd1\x80\xd0\xb3\xd1\x8b\xd0\xb7\xd1\x87\xd0\xb0, \xd0\x9a\xd1\x8b\xd1\x80\xd0\xb3\xd1\x8b\xd0\xb7 \xd1\x82\xd0\xb8\xd0\xbb\xd0\xb8",'Kirghiz');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KV', "kv", "kom", "kom", "Komi", "\xd0\xba\xd0\xbe\xd0\xbc\xd0\xb8 \xd0\xba\xd1\x8b\xd0\xb2",'Komi');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KG', "kg", "kon", "kon", "Kongo", "Kikongo",'Kongo');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KO', "ko", "kor", "kor", "Korean", "\xed\x95\x9c\xea\xb5\xad\xec\x96\xb4, \xec\xa1\xb0\xec\x84\xa0\xec\x96\xb4",'Korean');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KU', "ku", "kur", "kur", "Kurdish", "Kurd\xc3\xae, \xd9\x83\xd9\x88\xd8\xb1\xd8\xaf\xdb\x8c\xe2\x80\x8e",'Kurdish');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'KJ', "kj", "kua", "kua", "Kwanyama, Kuanyama", "Kuanyama".'Kwanyama, Kuanyama','Kwanyama, Kuanyama');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'LA', "la", "lat", "lat", "Latin", "latine, lingua latina",'Latin');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'LB', "lb", "ltz", "ltz", "Luxembourgish, Letzeburgesch", "L\xc3\xabtzebuergesch",'Luxembourgish, Letzeburgesch');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'LG', "lg", "lug", "lug", "Ganda", "Luganda",'Ganda');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'LI', "li", "lim", "lim", "Limburgish, Limburgan, Limburger", "Limburgs",'Limburgish ( Limburger)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'LN', "ln", "lin", "lin", "Lingala", "Ling\xc3\xa1la",'Lingala');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'LO', "lo", "lao", "lao", "Lao", "\xe0\xba\x9e\xe0\xba\xb2\xe0\xba\xaa\xe0\xba\xb2\xe0\xba\xa5\xe0\xba\xb2\xe0\xba\xa7",'Laothian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'LT', "lt", "lit", "lit", "Lithuanian", "lietuvi\xc5\xb3 kalba",'Lithuanian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'LU', "lu", "lub", "lub", "Luba-Katanga", "Tshiluba",'Luba-Katanga');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'LV', "lv", "lav", "lav", "Latvian", "latvie\xc5\xa1u valoda",'Latvian (Lettish)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'GV', "gv", "glv", "glv", "Manx", "Gaelg, Gailck",'Gaelic (Manx)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MK', "mk", "mkd", "mac", "Macedonian", "\xd0\xbc\xd0\xb0\xd0\xba\xd0\xb5\xd0\xb4\xd0\xbe\xd0\xbd\xd1\x81\xd0\xba\xd0\xb8 \xd1\x98\xd0\xb0\xd0\xb7\xd0\xb8\xd0\xba",'Macedonian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MG', "mg", "mlg", "mlg", "Malagasy", "fiteny malagasy",'Malagasy');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MS', "ms", "msa", "may", "Malay", "bahasa Melayu, \xd8\xa8\xd9\x87\xd8\xa7\xd8\xb3 \xd9\x85\xd9\x84\xd8\xa7\xd9\x8a\xd9\x88\xe2\x80\x8e",'Malay');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'ML', "ml", "mal", "mal", "Malayalam", "\xe0\xb4\xae\xe0\xb4\xb2\xe0\xb4\xaf\xe0\xb4\xbe\xe0\xb4\xb3\xe0\xb4\x82",'Malayalam');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MT', "mt", "mlt", "mlt", "Maltese", "Malti",'Maltese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MI', "mi", "mri", "mao", "M\xc4\x81ori", "te reo M\xc4\x81ori",'Maori');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MR', "mr", "mar", "mar", "Marathi (Mar\xc4\x81\xe1\xb9\xadh\xc4\xab)", "\xe0\xa4\xae\xe0\xa4\xb0\xe0\xa4\xbe\xe0\xa4\xa0\xe0\xa5\x80",'Marathi');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MH', "mh", "mah", "mah", "Marshallese", "Kajin M\xcc\xa7",'Marshallese');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MN', "mn", "mon", "mon", "Mongolian", "\xd0\x9c\xd0\xbe\xd0\xbd\xd0\xb3\xd0\xbe\xd0\xbb \xd1\x85\xd1\x8d\xd0\xbb",'Mongolian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'NA', "na", "nau", "nau", "Nauruan", "Dorerin Naoero",'Nauru');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'NV', "nv", "nav", "nav", "Navajo, Navaho", "Din\xc3\xa9 bizaad",'Navajo, Navaho');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'ND', "nd", "nde", "nde", "Northern Ndebele", "isiNdebele",'Northern Ndebele');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'NE', "ne", "nep", "nep", "Nepali", "\xe0\xa4\xa8\xe0\xa5\x87\xe0\xa4\xaa\xe0\xa4\xbe\xe0\xa4\xb2\xe0\xa5\x80",'Nepali');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'NG', "ng", "ndo", "ndo", "Ndonga", "Owambo",'Ndonga');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'NB', "nb", "nob", "nob", "Norwegian Bokm\xc3\xa5l", "Norsk bokm\xc3\xa5l","Norwegian Bokm\xc3\xa5l");//not  yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'NN', "nn", "nno", "nno", "Norwegian Nynorsk", "Norsk nynorsk",'Norwegian Nynorsk');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'NO', "no", "nor", "nor", "Norwegian", "Norsk",'Norwegian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'II', "ii", "iii", "iii", "Nuosu", "\xea\x86\x88\xea\x8c\xa0\xea\x92\xbf Nuosuhxop",'Nuosu');//not  yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'NR', "nr", "nbl", "nbl", "Southern Ndebele", "isiNdebele",'Southern Ndebele');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'OC', "oc", "oci", "oci", "Occitan", "occitan, lenga d'\xc3\xb2",'Occitan');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'OJ', "oj", "oji", "oji", "Ojibwe, Ojibwa", "\xe1\x90\x8a\xe1\x93\x82\xe1\x94\x91\xe1\x93\x88\xe1\x90\xaf\xe1\x92\xa7\xe1\x90\x8e\xe1\x93\x90",'Ojibwe, Ojibwa');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'CU', "cu", "chu", "chu", "Old Church Slavonic, Church Slavonic, Old Bulgarian", "\xd1\xa9\xd0\xb7\xd1\x8b\xd0\xba\xd1\x8a \xd1\x81\xd0\xbb\xd0\xbe\xd0\xb2\xd1\xa3\xd0\xbd\xd1\x8c\xd1\x81\xd0\xba\xd1\x8a",'Old Church Slavonic, Church Slavonic, Old Bulgarian');//not  yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'OM', "om", "orm", "orm", "Oromo", "Afaan Oromoo",'Oromo (Afan, Galla)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'OR_', "or", "ori", "ori", "Oriya", "\xe0\xac\x93\xe0\xac\xa1\xe0\xac\xbc\xe0\xac\xbf\xe0\xac\x86",'Oriya');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'OS', "os", "oss", "oss", "Ossetian, Ossetic", "\xd0\xb8\xd1\x80\xd0\xbe\xd0\xbd \xc3\xa6\xd0\xb2\xd0\xb7\xd0\xb0\xd0\xb3",'Ossetian, Ossetic');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'PA', "pa", "pan", "pan", "Panjabi, Punjabi", "\xe0\xa8\xaa\xe0\xa9\xb0\xe0\xa8\x9c\xe0\xa8\xbe\xe0\xa8\xac\xe0\xa9\x80, \xd9\xbe\xd9\x86\xd8\xac\xd8\xa7\xd8\xa8\xdb\x8c\xe2\x80\x8e",'Punjabi');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'PI', "pi", "pli", "pli", "P\xc4\x81li", "\xe0\xa4\xaa\xe0\xa4\xbe\xe0\xa4\xb4\xe0\xa4\xbf","P\xc4\x81li");//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'FA', "fa", "fas", "per", "Persian (Farsi)", "\xd9\x81\xd8\xa7\xd8\xb1\xd8\xb3\xdb\x8c",'Farsi');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'PL', "pl", "pol", "pol", "Polish", "j\xc4\x99zyk polski, polszczyzna",'Polish');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'PS', "ps", "pus", "pus", "Pashto, Pushto", "\xd9\xbe\xda\x9a\xd8\xaa\xd9\x88",'Pashto (Pushto)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'PT', "pt", "por", "por", "Portuguese", "portugu\xc3\xaas",'Portuguese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'QU', "qu", "que", "que", "Quechua", "Runa Simi, Kichwa",'Quechua');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'RM', "rm", "roh", "roh", "Romansh", "rumantsch grischun",'Rhaeto-Romance');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'RN', "rn", "run", "run", "Kirundi", "Ikirundi",'Kirundi (Rundi)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'RO', "ro", "ron", "rum", "Romanian", "limba rom\xc3\xa2n\xc4\x83",'Romanian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'RU', "ru", "rus", "rus", "Russian", "\xd0\xa0\xd1\x83\xd1\x81\xd1\x81\xd0\xba\xd0\xb8\xd0\xb9",'Russian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SA', "sa", "san", "san", "Sanskrit (Sa\xe1\xb9\x81sk\xe1\xb9\x9bta)", "\xe0\xa4\xb8\xe0\xa4\x82\xe0\xa4\xb8\xe0\xa5\x8d\xe0\xa4\x95\xe0\xa5\x83\xe0\xa4\xa4\xe0\xa4\xae\xe0\xa5\x8d",'Sanskrit');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SC', "sc", "srd", "srd", "Sardinian", "sardu",'Sardinian');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SD', "sd", "snd", "snd", "Sindhi", "\xe0\xa4\xb8\xe0\xa4\xbf\xe0\xa4\xa8\xe0\xa5\x8d\xe0\xa4\xa7\xe0\xa5\x80, \xd8\xb3\xd9\x86\xda\x8c\xd9\x8a\xd8\x8c \xd8\xb3\xd9\x86\xd8\xaf\xda\xbe\xdb\x8c\xe2\x80\x8e",'Sindhi');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SE', "se", "sme", "sme", "Northern Sami", "Davvis\xc3\xa1megiella",'Northern Sami');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SM', "sm", "smo", "smo", "Samoan", "gagana fa'a Samoa",'Samoan');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SG', "sg", "sag", "sag", "Sango", "y\xc3\xa2ng\xc3\xa2 t\xc3\xae s\xc3\xa4ng\xc3\xb6",'Sangro');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SR', "sr", "srp", "srp", "Serbian", "\xd1\x81\xd1\x80\xd0\xbf\xd1\x81\xd0\xba\xd0\xb8 \xd1\x98\xd0\xb5\xd0\xb7\xd0\xb8\xd0\xba",'Serbian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'GD', "gd", "gla", "gla", "Scottish Gaelic, Gaelic", "G\xc3\xa0idhlig",'Gaelic (Scottish)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SN', "sn", "sna", "sna", "Shona", "chiShona",'Shona');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SI', "si", "sin", "sin", "Sinhala, Sinhalese", "\xe0\xb7\x83\xe0\xb7\x92\xe0\xb6\x82\xe0\xb7\x84\xe0\xb6\xbd",'Sinhalese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SK', "sk", "slk", "slo", "Slovak", "sloven\xc4\x8dina, slovensk\xc3\xbd jazyk",'Slovak');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SL', "sl", "slv", "slv", "Slovene", "slovenski jezik, sloven\xc5\xa1\xc4\x8dina",'Slovenian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SO', "so", "som", "som", "Somali", "Soomaaliga, af Soomaali",'Somali');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'ST', "st", "sot", "sot", "Southern Sotho", "Sesotho",'Sesotho');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'ES', "es", "spa", "spa", "Spanish", "espa\xc3\xb1ol",'Spanish');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SU', "su", "sun", "sun", "Sundanese", "Basa Sunda",'Sundanese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SW', "sw", "swa", "swa", "Swahili", "Kiswahili",'Swahili (Kiswahili)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SS', "ss", "ssw", "ssw", "Swati", "SiSwati",'Siswati');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SV', "sv", "swe", "swe", "Swedish", "svenska",'Swedish');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TA', "ta", "tam", "tam", "Tamil", "\xe0\xae\xa4\xe0\xae\xae\xe0\xae\xbf\xe0\xae\xb4\xe0\xaf\x8d",'Tamil');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TE', "te", "tel", "tel", "Telugu", "\xe0\xb0\xa4\xe0\xb1\x86\xe0\xb0\xb2\xe0\xb1\x81\xe0\xb0\x97\xe0\xb1\x81",'Telugu');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TG', "tg", "tgk", "tgk", "Tajik", "\xd1\x82\xd0\xbe\xd2\xb7\xd0\xb8\xd0\xba\xd3\xa3, to\xc3\xa7ik\xc4\xab, \xd8\xaa\xd8\xa7\xd8\xac\xdb\x8c\xda\xa9\xdb\x8c\xe2\x80\x8e",'Tajik');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TH', "th", "tha", "tha", "Thai", "\xe0\xb9\x84\xe0\xb8\x97\xe0\xb8\xa2",'Thai');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TI', "ti", "tir", "tir", "Tigrinya", "\xe1\x89\xb5\xe1\x8c\x8d\xe1\x88\xad\xe1\x8a\x9b",'Tigrinya');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'BO', "bo", "bod", "tib", "Tibetan Standard, Tibetan, Central", "\xe0\xbd\x96\xe0\xbd\xbc\xe0\xbd\x91\xe0\xbc\x8b\xe0\xbd\xa1\xe0\xbd\xb2\xe0\xbd\x82",'Tibetan');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TK', "tk", "tuk", "tuk", "Turkmen", "T\xc3\xbcrkmen, \xd0\xa2\xd2\xaf\xd1\x80\xd0\xba\xd0\xbc\xd0\xb5\xd0\xbd",'Turkmen');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TL', "tl", "tgl", "tgl", "Tagalog", "Wikang Tagalog",'Tagalog');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TN', "tn", "tsn", "tsn", "Tswana", "Setswana",'Setswana');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TO', "to", "ton", "ton", "Tonga (Tonga Islands)", "faka Tonga",'Tonga');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TR', "tr", "tur", "tur", "Turkish", "T\xc3\xbcrk\xc3\xa7",'Turkish');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TS', "ts", "tso", "tso", "Tsonga", "Xitsonga",'Tsonga');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TT', "tt", "tat", "tat", "Tatar", "\xd1\x82\xd0\xb0\xd1\x82\xd0\xb0\xd1\x80 \xd1\x82\xd0\xb5\xd0\xbb\xd0\xb5, tatar tele",'Tatar');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TW', "tw", "twi", "twi", "Twi", "Twi",'Twi');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'TY', "ty", "tah", "tah", "Tahitian", "Reo Tahiti",'Tahitian');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'UG', "ug", "uig", "uig", "Uyghur", "\xd8\xa6\xdb\x87\xd9\x8a\xd8\xba\xdb\x87\xd8\xb1\xda\x86\xdb\x95\xe2\x80\x8e, Uyghurche",'Uighur');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'UK', "uk", "ukr", "ukr", "Ukrainian", "\xd0\xa3\xd0\xba\xd1\x80\xd0\xb0\xd1\x97\xd0\xbd\xd1\x81\xd1\x8c\xd0\xba\xd0\xb0",'Ukrainian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'UR', "ur", "urd", "urd", "Urdu", "\xd8\xa7\xd8\xb1\xd8\xaf\xd9\x88",'Urdu');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'UZ', "uz", "uzb", "uzb", "Uzbek", "O\xca\xbbzbek, \xd0\x8e\xd0\xb7\xd0\xb1\xd0\xb5\xd0\xba, \xd8\xa3\xdb\x87\xd8\xb2\xd8\xa8\xdb\x90\xd9\x83\xe2\x80\x8e",'Uzbek');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'VE', "ve", "ven", "ven", "Venda", "Tshiven\xe1\xb8\x93",'Venda');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'VI', "vi", "vie", "vie", "Vietnamese", "Ti\xe1\xba\xbfng Vi\xe1\xbb\x87t",'Vietnamese');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'VO', "vo", "vol", "vol", "Volap\xc3\xbck", "Volap\xc3\xbck",'Volapuk');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'WA', "wa", "wln", "wln", "Walloon", "walon",'Walloon');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'CY', "cy", "cym", "wel", "Welsh", "Cymraeg",'Welsh');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'WO', "wo", "wol", "wol", "Wolof", "Wollof",'Wolof');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'FY', "fy", "fry", "fry", "Western Frisian", "Frysk",'Frisian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'XH', "xh", "xho", "xho", "Xhosa", "isiXhosa",'Xhosa');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'YI', "yi", "yid", "yid", "Yiddish", "\xd7\x99\xd7\x99\xd6\xb4\xd7\x93\xd7\x99\xd7\xa9",'Yiddish');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'YO', "yo", "yor", "yor", "Yoruba", "Yor\xc3\xb9",'Yoruba');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'ZA', "za", "zha", "zha", "Zhuang, Chuang", "Sa\xc9\xaf cue\xc5\x8b\xc6\x85, Saw cuengh",'Zhuang, Chuang');//not yet defined in vidiun
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'ZU', "zu", "zul", "zul", "Zulu", "isiZulu",'Zulu');

        //The following cases are not part of language ISO , they are added to support backward compatibility
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'SH','sh','shc','shc','Serbo-Croatian','Serbo-Croatian' ,'Serbo-Croatian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'EN_GB', "en", "enb", "enb", "English (British)", "English (British)",'English (British)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'EN_US', "en", "enu", "enu", "English (American)", "English (American)",'English (American)');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MO','mo','mol','mol','Moldavian','Moldavian','Moldavian');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'MU','mu','mul','mul','Multilingual','Multilingual','Multilingual');
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,'UN', "un", "und", "und", "Undefined", "Undefined","Undefined");

        /*Extended support in ISO639-2/3/5*/
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AAL",null,null ,"aal","Afade","Afa\xc3\xab");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ABE",null,null ,"abe","Abnaki	 Western","W\xc3\xb4","banaki\xc3\xb4","dwaw\xc3\xb4gan");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ABQ",null,null ,"abq","Abaza","\xd0\xb0\xd0\xb1\xd0\xb0\xd0\xb7\xd0\xb0");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ABU",null,null ,"abu","Abure","\xc9\x94"."bule \xc9\x94y\xca\x8b\xc9\x9b");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ACE",null,"ace","ace","Achinese","Aceh");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ACF",null,null ,"acf","Saint Lucian Creole French","Kw\xc3\xa9y\xc3\xb2l");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ACN",null,null ,"acn","Achang","M\xc3\xb6nghsa");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ACT",null,null ,"act","Achterhooks","Achterhooks");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ACV",null,null ,"acv","Achumawi","Aj\xc3\xbamm\xc3\xa1\xc3\xa1w\xc3\xad");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ADJ",null,null ,"adj","Adioukrou","M\xc9\x94jukru");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ADT",null,null ,"adt","Adynyamathanha","Yura Ngawarla");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ADY",null,"ady","ady","Adyghe; Adygei","\xd0\xb0\xd0\xb4\xd1\x8b\xd0\xb3\xd1\x8d\xd0\xb1\xd0\xb7\xd1\x8d");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AGQ",null,null ,"agq","Aghem","Agh\xc3\xadm");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AGR",null,null ,"agr","Aguaruna","Awajun");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AGU",null,null ,"agu","Aguacateco","Awakateko");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AGX",null,null ,"agx","Aghul","\xd0\xb0\xd0\xb3\xd1\x8a\xd1\x83\xd0\xbb");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AII",null,null ,"aii","Assyrian Neo-Aramaic","\xdc\xa3\xdc\x98\xdc\xaa\xdc\x9d\xdc\x9d\xdc\x90 \xdc\xa3\xdc\x98\xdc\xaa\xdc\x9d\xdc\xac	\xdc\x90\xdc\xac\xdc\x98\xdc\xaa\xdc\x9d\xdc\x90 \xdc\xa3\xdc\x98\xdc\xaa\xdc\x9d\xdc\x9d\xdc\x90");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AIN",null,"ain","ain","Ainu (Japan);","\xe3\x82\xa2\xe3\x82\xa4\xe3\x83\x8c \xe3\x82\xa4\xe3\x82\xbf\xe3\x82\xaf(\xe3\x82\xa4\xe3\x82\xbf\xe3\x83\x83\xe3\x82\xaf);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AKK",null,"akk","akk","Akkadian","Akkad\xc3\xbb");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AKL",null,null ,"akl","Aklanon","Inakeanon");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AKU",null,null ,"aku","Akum","Aakuem");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AKZ",null,null ,"akz","Alabama","Albaamo innaa\xc9\xaciilka");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ALC",null,null ,"alc","Qawasqar","Alacalufe");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ALE",null,"ale","ale","Aleut","Unangax tunuu");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ALN",null,null ,"aln","Albanian (Gheg);","Gegnisht");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ALQ",null,null ,"alq","Algonquin","Anishnaabemowin (Omaamiwininimowin);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ALS",null,null ,"als","Albanian (Tosk);","Tosk\xc3\xabrishte");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ALT",null,"alt","alt","Altai (Southern);","\xd0\x90\xd0\xbb\xd1\x82\xd0\xb0\xd0\xb9 \xd1\x82\xd0\xb8\xd0\xbb\xd0\xb8");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AME",null,null ,"ame","Yanesha'","Yane\xc5\xa1"."a\xc4\x8d");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AMW",null,null ,"amw","Western Neo-Aramaic","\xdc\x90\xdc\xaa\xdc\xa1\xdc\x9d\xdc\xac	 \xd8\xa2\xd8\xb1\xd8\xa7\xd9\x85\xd9\x8a");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ANG",null,"ang","ang","Old English","Englisc");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"APJ",null,null ,"apj","Apache (Jicarilla);","Ab\xc3\xa1"."achi mizaa");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"APW",null,null ,"apw","Apache (Western);","Nd\xc3\xa9\xc3\xa9 biy\xc3\xa1ti'");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ARB",null,null ,"arb","Arabic (standard);","\xd9\x84\xd8\xb9\xd8\xb1\xd8\xa8\xd9\x8a\xd8\xa9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ARC",null,"arc","arc","Aramaic","\xdc\x90\xdc\xaa\xdc\xa1\xdc\x9d\xdc\x90");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ARI",null,null ,"ari","Arikara","S\xc3\xa1hni\xc5\xa1");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ARN",null,"arn","arn","Araucanian","Mapudungun");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ARP",null,"arp","arp","Arapaho","Hinono'eitiit");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ASB",null,null ,"asb","Assiniboine","Nak\xca\xb0\xc3\xb3"."da");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AST",null,"ast","ast","Asturian","Asturianu");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ATJ",null,null ,"atj","Atikamekw","Atikamekw");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AUC",null,null ,"auc","Waorani","Huao Terero");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AVK",null,null ,"avk","Kotava","Kotava");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"AWA",null,"awa","awa","Awadhi","\xe0\xa4\x86\xe0\xa4\xb5\xe0\xa4\xa7\xe0\xa5\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"B_T",null,null ,"b-t","Arabic	 Tunisian Spoken","\xd8\xaa\xd9\x88\xd9\x86\xd8\xb3\xd9\x8a");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BAL",null,"bal","bal","Baluchi","\xd8\xa8\xd9\x84\xd9\x88\xda\x86\xdb\x8c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BAN",null,"ban","ban","Balinese","Basa Bali");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BAR",null,null ,"bar","Bavarian","Bairisch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BAS",null,"bas","bas","Basa (Cameroon);","\xc9\x93"."asa\xc3\xa1");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BCC",null,null ,"bcc","Balochi	 Southern","\xd8\xa8\xd9\x84\xd9\x88\xda\x86\xdb\x8c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BCR",null,null ,"bcr","Babine","Witsuwit'en");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BDJ",null,null ,"bdj","Bai","Bairt\xe2\xa4\xa7ngvrt\xe2\xa4\xa7zix");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BEA",null,null ,"bea","Beaver","Dunne-za");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BEJ",null,"bej","bej","Beja","\xd8\xa8\xd8\xaf\xd8\xa7\xd9\x88\xd9\x8a\xd8\xa9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BEM",null,"bem","bem","Bemba (Zambia);","Ichibemba");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BEW",null,null ,"bew","Betawi","Bahasa Betawi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BFQ",null,null ,"bfq","Badaga","\xe0\xb2\xac\xe0\xb2\xa1\xe0\xb2\x97");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BFT",null,null ,"bft","Balti","\xd8\xa8\xd9\x84\xd8\xaa\xdb\x8c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BHB",null,null ,"bhb","Bhili","\xe0\xa4\xad\xe0\xa5\x80\xe0\xa4\xb2\xe0\xa5\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BHO",null,"bho","bho","Bhojpuri","\xe0\xa4\xad\xe0\xa5\x8b\xe0\xa4\x9c\xe0\xa4\xaa\xe0\xa5\x81\xe0\xa4\xb0\xe0\xa5\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BIK",null,"bik","bik","Bikol","Bicol");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BIN",null,"bin","bin","Bini","\xc3\x88"."d\xc3\xb3");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BPY",null,null ,"bpy","Bishnupriya Manipuri","\xe0\xa6\xac\xe0\xa6\xbf\xe0\xa6\xb7\xe0\xa7\x8d\xe0\xa6\xa3\xe0\xa7\x81\xe0\xa6\xaa\xe0\xa7\x8d\xe0\xa6\xb0\xe0\xa6\xbf\xe0\xa6\xaf\xe0\xa6\xbc\xe0\xa6\xbe \xe0\xa6\xae\xe0\xa6\xa3\xe0\xa6\xbf\xe0\xa6\xaa\xe0\xa7\x81\xe0\xa6\xb0\xe0\xa7\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BUA",null,"bua","bua","Buriat","\xd0\xb1\xd1\x83\xd1\x80\xd1\x8f\xd0\xb0\xd0\xb4");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"BUG",null,"bug","bug","Buginese","\xe1\xa8\x85\xe1\xa8\x94 \xe1\xa8\x95\xe1\xa8\x98\xe1\xa8\x81\xe1\xa8\x97");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CAA",null,null ,"caa","Chort\xc3\xad","\xc4\x8dorti'");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CAD",null,"cad","cad","Caddo","Has\xc3\xad:nay");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CAF",null,null ,"caf","Carrier	 Southern","\xe1\x91\x95\xe1\x97\xb8\xe1\x92\xa1");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CAY",null,null ,"cay","Cayuga","Goyogo\xcc\xb1h\xc3\xb3:n\xc7\xab\xe2\x80\x99");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CDO",null,null ,"cdo","Min Dong Chinese","\xe9\x96\xa9\xe6\x9d\xb1\xe8\xaa\x9e");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CEB",null,"ceb","ceb","Cebuano","S(in);ugboanon");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CHC",null,null ,"chc","Catawba","Iyeye");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CHG",null,"chg","chg","Chagatai","\xd8\xac\xd8\xba\xd8\xaa\xd8\xa7\xdb\x8c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CHM",null,"chm","chm","Mari (Russia);","\xd0\xbc\xd0\xb0\xd1\x80\xd0\xb8\xd0\xb9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CHN",null,"chn","chn","Chinook jargon","Chinuk wawa");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CHO",null,"cho","cho","Choctaw","Chahta");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CHP",null,"chp","chp","Chipewyan","\xe1\x91\x8c\xe1\x93\x80\xe1\x93\xb2\xe1\x92\xa2\xe1\x95\x84\xe1\x93\x80\n(D\xc3\xabne S\xc5\xb3\xc5\x82in\xc3\xa9);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CHR",null,"chr","chr","Cherokee","\xe1\x8f\xa3\xe1\x8e\xb3\xe1\x8e\xa9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CHY",null,"chy","chy","Cheyenne","Ts\xc3\xaah\xc3\xa9st");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CIC",null,null ,"cic","Chickasaw","Chikasha");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CIM",null,null ,"cim","Cimbrian","Zimbrisch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CIW",null,null ,"ciw","Chippewa","\xe1\x90\x8a\xe1\x93\x82\xe1\x90\xa6\xe1\x94\x91\xe1\x93\x88\xe1\x90\xaf\xe1\x92\xa7\xe1\x90\xa7\xe1\x90\x83\xe1\x93\x90 / \xe1\x90\x85\xe1\x92\x8b\xe1\x90\xa7\xe1\x90\xaf\xe1\x92\xa7\xe1\x90\xa7\xe1\x90\x83\xe1\x93\x90\n(Anishinaabemowin / Ojibwemowin);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CJS",null,null ,"cjs","Shor","\xd0\xa8\xd0\xbe\xd1\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CJY",null,null ,"cjy","Jinyu Chinese","\xe6\x99\x8b\xe8\xaf\xad");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CKT",null,null ,"ckt","Chukot","\xd1\x87\xd0\xb0\xd1\x83\xd1\x87\xd1\x83");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CLC",null,null ,"clc","Chilcotin","T\xc5\xa1inlhqot\xe2\xa4\x99in	 Tsilhqot\xe2\x80\x99in");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CLD",null,null ,"cld","Chaldean Neo-Aramaic","\xdc\x9f\xdc\xa0\xdc\x95\xdc\x9d\xdc\x90");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CLM",null,null ,"clm","Clallam	 Klallam","N\xc9\x99x\xca\xb7s\xc6\x9b\xca\xbc"."ay\xca\xbc\xc9\x99m\xca\xbc\xc3\xba"."c\xc9\x99n");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CMN",null,null ,"cmn","Mandarin Chinese","\xe5\xae\x98\xe8\xa9\xb1; \xe5\x8c\x97\xe6\x96\xb9\xe8\xa9\xb1");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"COC",null,null ,"coc","Cocopa","Kwikapa");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"COJ",null,null ,"coj","Cochimi","Tipai");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"COM",null,null ,"com","Comanche","N\xca\x89m\xca\x89 tekwap\xca\x89\xcc\xb1");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"COO",null,null ,"coo","Comox","Sa\xc9\xacu\xc9\xactx\xca\xb7");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"COP",null,"cop","cop","Coptic","\xe2\xb2\x99\xe2\xb2\x89\xe2\xb2\xa7\xe2\xb2\x9b\xcc\x80\xe2\xb2\xa3\xe2\xb2\x89\xe2\xb2\x99\xe2\xb2\x9b\xcc\x80\xe2\xb2\xad\xe2\xb2\x8f\xe2\xb2\x99\xe2\xb2\x93");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CRH",null,"crh","crh","Crimean Tatar","\xd0\x9a\xd1\x8a\xd1\x8b\xd1\x80\xd1\x8b\xd0\xbc \xd0\xa2\xd0\xb0\xd1\x82\xd0\xb0\xd1\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CRX",null,null ,"crx","Carrier","\xe1\x91\x95\xe1\x97\xb8\xe1\x92\xa1");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CSB",null,"csb","csb","Kashubian","Kasz\xc3\xab"."bsczi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CUP",null,null ,"cup","Cupe\xc3\xb1o","Kuupangaxwichem");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"CZH",null,null ,"czh","Huizhou Chinese","\xe5\xbe\xbd\xe5\xb7\x9e\xe8\xaf\x9d");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DAK",null,"dak","dak","Dakota","Lakhota");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DAR",null,"dar","dar","Dargwa","\xd0\xb4\xd0\xb0\xd1\x80\xd0\xb3\xd0\xb0\xd0\xbd");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DDO",null,null ,"ddo","Tsez","\xd1\x86\xd0\xb5\xd0\xb7");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DEL",null,"del","del","Delaware","L\xc3\xabnape");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DGR",null,"dgr","dgr","Dogrib","T\xc5\x82\xc4\xaf"."ch\xc7\xab");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DHV",null,null ,"dhv","Dehu","Drehu");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DIH",null,null ,"dih","Kumiai","K'miai");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DIN",null,"din","din","Dinka","Thu\xc9\x94\xc5\x8bj\xc3\xa4\xc5\x8b");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DJE",null,null ,"dje","Zarma","Zarmaciine");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DLG",null,null ,"dlg","Dolgan","\xd0\x94\xd1\x83\xd0\xbb\xd2\x95\xd0\xb0\xd0\xbd");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DNG",null,null ,"dng","Dungan","\xd0\xa5\xd1\x83\xd1\x8d\xd0\xb9\xd0\xb7\xd1\x9e \xd0\xb9\xd2\xaf\xd1\x8f\xd0\xbd (Huejzw jyian);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DOH",null,null ,"doh","Dong","Leec Gaeml");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DOI",null,"doi","doi","Dogri (generic);","\xe0\xa4\xa1\xe0\xa5\x8b\xe0\xa4\x97\xe0\xa4\xb0\xe0\xa5\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DSB",null,"dsb","dsb","Sorbian	 Lower","Dolnoserbski");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DUA",null,"dua","dua","Duala","Duala");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"DYU",null,"dyu","dyu","Dyula","Julakan");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"EEE",null,null ,"eee","E","E");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"EGL",null,null ,"egl","Emilian","Emigli\xc3\xa0\xe1\xb9\x85");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ENM",null,"enm","enm","English	 Middle (1100-1500);","English");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"EVE",null,null ,"eve","Even","\xd1\x8d\xd0\xb2\xd1\x8d\xd0\xb4\xd1\x8b");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"EVN",null,null ,"evn","Evenki","\xd0\xbe\xd1\x80\xd0\xbe\xd1\x87\xd0\xbe\xd0\xbd");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FAN",null,"fan","fan","Fang (Equatorial Guinea);","Fang");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FAX",null,null ,"fax","Fala","Fala");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FIL",null,"fil","fil","Filipino","Filipino");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FIT",null,null ,"fit","Finnish (Tornedalen);","Me\xc3\xa4nkieli");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FON",null,"fon","fon","Fon","F\xc9\x94ngb\xc3\xa8");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FRK",null,null ,"frk","Frankish","Fr\xc3\xa4nkisch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FRP",null,null ,"frp","Franco-Proven\xc3\xa7"."al","Francoprovensal");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FRR",null,"frr","frr","Frisian	 Northern","Nordfriisk");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FUC",null,null ,"fuc","Pulaar","Pulaar");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FUR",null,"fur","fur","Friulian","Furlan");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"FVR",null,null ,"fvr","Fur","F\xc3\xb2\xc3\xb2r");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GAA",null,"gaa","gaa","Ga","G\xc3\xa3");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GAG",null,null ,"gag","Gagauz","Gagauz dili");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GAN",null,null ,"gan","Gan","\xe8\xb5\xa3\xe8\xaf\xad");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GBZ",null,null ,"gbz","Dari	 Zoroastrian","\xd8\xaf\xd9\x8e\xd8\xb1\xd9\x90\xd9\x8a");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GDM",null,null ,"gdm","Laal","Y\xc9\x99w l\xc3\xa1\xc3\xa0:l");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GEH",null,null ,"geh","German	 Hutterite","Hutterisch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GEZ",null,"gez","gez","Geez","\xe1\x8c\x8d\xe1\x8b\x95\xe1\x8b\x9d");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GIL",null,"gil","gil","Gilbertese","Taetae ni Kiribati");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GIT",null,null ,"git","Gitxsan","Gitx\xcc\xb1sanimx\xcc\xb1");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GLD",null,null ,"gld","Nanai","\xd0\xbd\xd0\xb0\xd0\xbd\xd0\xb0\xd0\xb9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GOM",null,null ,"gom","Konkani	 Goan","\xe0\xb2\x95\xe0\xb3\x8a\xe0\xb2\x82\xe0\xb2\x95\xe0\xb2\xa3\xe0\xb2\xbf");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GRC",null,"grc","grc","Greek	 Ancient (to 1453);","\xe1\xbc\x91\xce\xbb\xce\xbb\xce\xb7\xce\xbd\xce\xb9\xce\xba\xce\xac");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GSW",null,"gsw","gsw","Swiss German	 Alemannic	 Alsatian","Schwyzerd\xc3\xbctsch	 Alemannisch	 Elsassisch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"GWI",null,"gwi","gwi","Gwich\xc2\xb4in","Gwich\xc2\xb4in");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HAA",null,null ,"haa","Han","H\xc3\xa4n");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HAI",null,"hai","hai","Haida","X\xcc\xb2"."aat K\xc3\xadl");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HAS",null,null ,"has","Haisla","X\xcc\x84"."a'\xe2\x80\x99islak\xcc\x93"."ala");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HAW",null,"haw","haw","Hawaiian","\xca\xbb\xc5\x8dlelo Hawai\xca\xbbi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HEI",null,null ,"hei","Heiltsuk","Hailhzaqvla");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HID",null,null ,"hid","Hidatsa","Hiraac\xc3\xa1");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HIL",null,"hil","hil","Hiligaynon","Ilonggo");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HMN",null,"hmn","hmn","Hmong","Hmoob");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HNI",null,null ,"hni","Hani","Haqniqdoq");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HOP",null,null ,"hop","Hopi","Hopil\xc3\xa0vayi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HSB",null,"hsb","hsb","Sorbian	 Upper","Hornjoserbsce");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HUR",null,null ,"hur","Halkomelem","H\xc7\x9dn\xcc\x93q\xcc\x93\xc7\x9dmin\xcc\x93\xc7\x9dm\xcc\x93");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ILO",null,"ilo","ilo","Iloko","Ilokano");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"INH",null,"inh","inh","Ingush","\xd0\xb3\xd3\x80\xd0\xb0\xd0\xbb\xd0\xb3\xd3\x80\xd0\xb0\xd0\xb9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ITL",null,null ,"itl","Itelmen","\xd0\x98\xd1\x82\xd1\x8d\xd0\xbd\xd0\xbc\xd1\x8d\xd0\xbd");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"IZH",null,null ,"izh","Ingrian","I\xc5\xbeoran keeli");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"JBO",null,"jbo","jbo","Lojban","La .lojban.");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"JCT",null,null ,"jct","Judeo-Crimean Tatar","\xd0\x9a\xd1\x8a\xd1\x80\xd1\x8b\xd0\xbc\xd1\x87\xd0\xb0\xd1\x85");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"JGE",null,null ,"jge","Judeo-Georgian","\xd7\xa7\xd7\x99\xd7\x91\xd7\xa8\xd7\x95\xd7\x9c\xd7\x99");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"JUT",null,null ,"jut","Jutish","Jysk");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KAA",null,"kaa","kaa","Karakalpak","\xd2\x9a\xd0\xb0\xd1\x80\xd0\xb0\xd2\x9b\xd0\xb0\xd0\xbb\xd0\xbf\xd0\xb0\xd2\x9b");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KAB",null,"kab","kab","Kabyle","Taqbaylit");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KAJ",null,null ,"kaj","Jju","Kaje");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KAP",null,null ,"kap","Bezhta","\xd0\x91\xd0\xb5\xd0\xb6\xd0\xba\xd1\x8c\xd0\xb0");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KAW",null,"kaw","kaw","Kawi","Bh\xc4\x81\xe1\xb9\xa3"."a Kawi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KBD",null,"kbd","kbd","Kabardian","\xd0\xba\xd1\x8a\xd1\x8d\xd0\xb1\xd1\x8d\xd1\x80\xd0\xb4\xd0\xb5\xd0\xb8\xd0\xb1\xd0\xb7\xd1\x8d");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KCA",null,null ,"kca","Khanty","\xd1\x85\xd0\xb0\xd0\xbd\xd1\x82\xd1\x8b");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KDR",null,null ,"kdr","Karaim","\xd0\x9a\xd1\x8a\xd0\xb0\xd1\x80\xd0\xb0\xd0\xb9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KEA",null,null ,"kea","Kabuverdianu","Kriolu kabuverdianu");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KFA",null,null ,"kfa","Kodava","\xe0\xb2\x95\xe0\xb3\x8a\xe0\xb2\xa1\xe0\xb2\xb5");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KFR",null,null ,"kfr","Kachchi","\xe0\xaa\x95\xe0\xaa\x9a\xe0\xab\x8d\xe0\xaa\x9a\xe0\xaa\xbf");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KHA",null,"kha","kha","Khasi","Khasi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KIC",null,null ,"kic","Kickapoo","Kikap\xc3\xba");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KIM",null,null ,"kim","Karagas","\xd0\xa2\xd0\xbe\xd1\x8a\xd1\x84\xd0\xb0");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KIO",null,null ,"kio","Kiowa","C\xc3\xa1uijo\xcc\xb1:g\xc3\xa0");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KJH",null,null ,"kjh","Khakas","\xd0\xa5\xd0\xb0\xd0\xba\xd0\xb0\xd1\x81\xd1\x87\xd0\xb0");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KJV",null,null ,"kjv","Kaikavian literary language (Kajkavian);","Kajkavski");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KKZ",null,null ,"kkz","Kaska","Dene Dzage");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KLJ",null,null ,"klj","Khalaj	 Turkic","Qalayce");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KNN",null,null ,"knn","Konkani (specific);","\xe0\xb2\x95\xe0\xb3\x8a\xe0\xb2\x82\xe0\xb2\x95\xe0\xb2\xa3\xe0\xb2\xbf");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KOI",null,null ,"koi","Komi-Permyak","\xd0\xbf\xd0\xb5\xd1\x80\xd1\x8b\xd0\xbc-\xd0\xba\xd0\xbe\xd0\xbc\xd0\xb8");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KOK",null,"kok","kok","Konkani (generic);","\xe0\xa4\x95\xe0\xa5\x8a\xe0\xa4\x82\xe0\xa4\x95\xe0\xa4\xa3\xe0\xa4\xbf");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KOS",null,"kos","kos","Kosraean","Kosrae");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KPE",null,"kpe","kpe","Kpelle","Kpele");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KPO",null,null ,"kpo","Ikposo","Akp\xc9\x94ss\xc9\x94");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KPY",null,null ,"kpy","Koryak","\xd0\xbd\xd1\x8b\xd0\xbc\xd1\x8b\xd0\xbb\xd0\xb0\xd0\xbd");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KRC",null,"krc","krc","Karachay-Balkar","\xd0\x9a\xd1\x8a\xd0\xb0\xd1\x80\xd0\xb0\xd1\x87\xd0\xb0\xd0\xb9-\xd0\x9c\xd0\xb0\xd0\xbb\xd0\xba\xd1\x8a\xd0\xb0\xd1\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KRL",null,"krl","krl","Karelian","Karjala");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KSH",null,null ,"ksh","Colognian","K\xc3\xb6lsch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KSK",null,null ,"ksk","Kansa","Ka\xc3\xa1\xe2\x81\xbfze");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KUM",null,"kum","kum","Kumyk","\xd0\x9a\xd1\x8a\xd1\x83\xd0\xbc\xd1\x83\xd0\xba\xd1\x8a");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KUT",null,"kut","kut","Kutenai","Ktunaxa");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KWK",null,null ,"kwk","Kwakiutl","Kwak\xcc\x93wala");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"KXM",null,null ,"kxm","Khmer	 Northern","\xe1\x9e\x81\xe1\x9f\x92\xe1\x9e\x98\xe1\x9f\x82\xe1\x9e\x9a\xe1\x9e\x9b\xe1\x9e\xbe");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LAD",null,"lad","lad","Ladino","\xd7\x92'\xd7\x95\xd7\x93\xd7\x99\xd7\x90\xd7\x95\xe2\x80\x93\xd7\x90\xd7\x99\xd7\xa1\xd7\xa4\xd7\x90\xd7\xa0\xd7\x99\xd7\x99\xd7\x95\xd7\x9c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LAH",null,"lah","lah","Lahnda","\xe0\xa8\xb2\xe0\xa8\xb9\xe0\xa8\xbf\xe0\xa9\xb0\xe0\xa8\xa6\xe0\xa9\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LBE",null,null ,"lbe","Lak","\xd0\xbb\xd0\xb0\xd0\xba\xd0\xba\xd1\x83");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LEZ",null,"lez","lez","Lezghian","\xd0\xbb\xd0\xb5\xd0\xb7\xd0\xb3\xd0\xb8");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LHU",null,null ,"lhu","Lahu","La\xcb\x87hu\xcb\x8d hkaw\xcb\x87");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LIF",null,null ,"lif","Limbu","\xe1\xa4\x9b\xe1\xa4\xa1\xe1\xa4\x96\xe1\xa4\xa1\xe1\xa4\x88\xe1\xa4\xa8\xe1\xa4\x85");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LIJ",null,null ,"lij","Ligurian","L\xc3\xadguru");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LIL",null,null ,"lil","Lillooet","S\xc6\x9b\xe2\x80\x99"."a\xc6\x9b\xe2\x80\x99imx\xc7\x9d"."c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LKI",null,null ,"lki","Laki","\xd9\x84\xd9\x87 \xda\xa9\xdb\x8c \xd9\x84\xd9\x87 \xda\xa9\xd8\xb3\xd8\xaa\xd8\xa7\xd9\x86");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LKT",null,null ,"lkt","Lakota","Lak\xc8\x9f\xc3\xb3tiyapi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LLD",null,null ,"lld","Ladin","Ladin");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LMO",null,null ,"lmo","Lombard","Lumbard");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LOM",null,null ,"lom","Loma (Liberia);","L\xc3\xb6(g);\xc3\xb6m\xc3\xa0g\xc3\xb2\xc3\xb2i");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LOZ",null,"loz","loz","Lozi","SiLozi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LUA",null,"lua","lua","Luba-Lulua","Lwa\xc3\xa0:");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LUD",null,null ,"lud","Ludian","L\xc3\xbc\xc3\xbc"."di");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LUN",null,"lun","lun","Lunda","ChiLunda");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LUO",null,"luo","luo","Luo (Kenya and Tanzania);","Dholuo");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LUQ",null,null ,"luq","Lucumi","Lucum\xc3\xad");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LUT",null,null ,"lut","Lushootseed","D\xc9\x99x\xca\xb7l\xc9\x99\xc5\xa1ucid");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"LZZ",null,null ,"lzz","Laz","\xe1\x83\x9a\xe1\x83\x90\xe1\x83\x96\xe1\x83\xa3\xe1\x83\xa0\xe1\x83\x98");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MAD",null,"mad","mad","Madurese","Basa Mathura");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MAG",null,"mag","mag","Magahi","\xe0\xa4\xae\xe0\xa4\x97\xe0\xa4\xb9\xe0\xa5\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MAI",null,"mai","mai","Maithili","\xe0\xa4\xae\xe0\xa5\x88\xe0\xa4\xa5\xe0\xa4\xbf\xe0\xa4\xb2\xe0\xa5\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MAS",null,"mas","mas","Masai","\xc9\x94l Maa");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MDF",null,"mdf","mdf","Moksha","\xd0\xbc\xd0\xbe\xd0\xba\xd1\x88\xd0\xb0");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MEN",null,"men","men","Mende (Sierra Leone);","M\xc9\x9bnde");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MEZ",null,null ,"mez","Menominee","Om\xc4\x81\xc4\x93qnomenew");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MFE",null,null ,"mfe","Morisyen","Morisyin");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MFY",null,null ,"mfy","Mayo","Ca\xc3\xadta");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MHQ",null,null ,"mhq","Mandan","R\xc5\xb3\xcc\x81\xca\xbc"."eta:re");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MIC",null,"mic","mic","Micmac","Mi'gmaq");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MID",null,null ,"mid","Mandaic","Mand\xc4\x81y\xc3\xac");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MIN",null,"min","min","Minangkabau","Baso Minangkabau");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MNC",null,"mnc","mnc","Manchu","\xe1\xa0\xae\xe1\xa0\xa0\xe1\xa0\xa8\xe1\xa0\xb5\xe1\xa1\xa0 \xe1\xa1\xa4\xe1\xa1\xb3\xe1\xa0\xb0\xe1\xa1\xa0\xe1\xa0\xa8\xe2\x80\xaf\xe1\xa0\xaa\xe1\xa1\x9d");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MNI",null,"mni","mni","Meitei","\xe0\xa6\xae\xe0\xa7\x88\xe0\xa6\x87\xe0\xa6\xa4\xe0\xa7\x88\xe0\xa6\x87\xe0\xa6\xb2\xe0\xa7\x8b\xe0\xa6\xa8");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MNP",null,null ,"mnp","Min Bei Chinese","\xe9\x97\xbd\xe5\x8c\x97");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MNS",null,null ,"mns","Mansi","\xd0\xbc\xd0\xb0\xd0\xbd\xd1\x8c\xd1\x81\xd0\xb8");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MNW",null,null ,"mnw","Mon","\xe1\x80\x98\xe1\x80\xac\xe1\x80\x9e\xe1\x80\xac\xe1\x80\x99\xe1\x80\x94\xe1\x80\xb9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MOH",null,"moh","moh","Mohawk","Kanien\xe2\x80\x99k\xc3\xa9ha");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MOS",null,"mos","mos","Mossi","M\xc3\xb2or\xc3\xa9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MRW",null,null ,"mrw","Maranao","Austronesian");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MTQ",null,null ,"mtq","Muong","M\xc6\xb0\xe1\xbb\x9dng");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MUS",null,"mus","mus","Creek","Mvskok\xc4\x93");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MWL",null,"mwl","mwl","Mirandese","Mirand\xc3\xaas");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MWR",null,"mwr","mwr","Marwari","\xe0\xa4\xae\xe0\xa4\xbe\xe0\xa4\xb0\xe0\xa4\xb5\xe0\xa4\xbe\xe0\xa4\xa1\xe0\xa4\xbc\xe0\xa5\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MXI",null,null ,"mxi","Mozarabic","\xd9\x85\xd9\x8f\xd8\xb2\xd9\x8e\xd8\xb1\xd9\x8e\xd8\xa8");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MYP",null,null ,"myp","Pirah\xc3\xa3","Hi'aiti'ihi'");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MYV",null,"myv","myv","Erzya","\xd1\x8d\xd1\x80\xd0\xb7\xd1\x8f");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"MZB",null,null ,"mzb","Tumzabt","\xd8\xaa\xd9\x88\xd9\x85\xd8\xb2\xd8\xa7\xd8\xa8\xd8\xaa");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NAP",null,"nap","nap","Neapolitan","Nnapulitano");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NAQ",null,null ,"naq","Nama (Namibia);","Khoekhoegowab");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NCG",null,null ,"ncg","Nisga'a","Nis\xc7\xa5"."a\xe2\x80\x99"."a");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NDS",null,"nds","nds","Low German; Low Saxon","Plattd\xc3\xbc\xc3\xbctsch; Neddersass'sch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NEW",null,"new","new","Newari; Nepal Bhasa","\xe0\xa4\xa8\xe0\xa5\x87\xe0\xa4\xaa\xe0\xa4\xbe\xe0\xa4\xb2 \xe0\xa4\xad\xe0\xa4\xbe\xe0\xa4\xb7\xe0\xa4\xbe");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NIO",null,null ,"nio","Nganasan","\xd0\xbd\xd0\xb3\xd0\xb0\xd0\xbd\xd0\xb0\xd1\x81\xd0\xb0\xd0\xbd\xd1\x8b");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NIU",null,"niu","niu","Niuean","Ko e vagahau Niu\xc4\x93");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NIV",null,null ,"niv","Gilyak; Nivkh","\xd0\xbd\xd0\xb8\xd0\xb2\xd1\x85\xd0\xb3\xd1\x83");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NOD",null,null ,"nod","Thai (Northern);","\xe0\xb8\xa5\xe0\xb9\x89\xe0\xb8\xb2\xe0\xb8\x99\xe0\xb8\x99\xe0\xb8\xb2");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NOG",null,"nog","nog","Nogai","\xd0\x9d\xd0\xbe\xd0\xb3\xd0\xb0\xd0\xb9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NON",null,"non","non","Norse	 Old","Norr\xc7\xbfna");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NOV",null,null ,"nov","Novial","Novial");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NSK",null,null ,"nsk","Naskapi","\xe1\x93\x87\xe1\x94\x85\xe1\x91\xb2\xe1\x90\xb1");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NSO",null,"nso","nso","Northern Sotho	 Pedi; Sepedi","SeP\xc3\xaa"."di");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NYM",null,"nym","nym","Nyamwezi","Kinyamwezi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NYO",null,"nyo","nyo","Nyoro","Runyoro");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"NYS",null,null ,"nys","Nyungah","Noongar");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"OJB",null,null ,"ojb","Ojibwa	 Northwestern","Anishinaabemowin (Ojibwemowin);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"OJC",null,null ,"ojc","Ojibwa	 Central","Anishinaabemowin (Ojibwemowin);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"OJG",null,null ,"ojg","Ojibwa	 Eastern","Nishnaabemwin (Jibwemwin);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"OJS",null,null ,"ojs","Ojibwa	 Severn","\xe1\x90\x8a\xe1\x93\x82\xe1\x94\x91\xe1\x93\x82\xe1\x93\x82\xe1\x92\xa7\xe1\x90\x8e\xe1\x93\x90 (Anishininiimowin);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"OJW",null,null ,"ojw","Ojibwa	 Western","Anih\xc5\xa1in\xc4\x81p\xc4\x93mowin (Nakaw\xc4\x93mowin);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ONE",null,null ,"one","Oneida","On\xca\x8cyota\xe2\x80\x99"."a:ka");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ONO",null,null ,"ono","Onondaga","On\xc7\xabta\xe2\x80\x99k\xc3\xa9ka\xe2\x80\x99");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"OOD",null,null ,"ood","Tohono O'odham","O'odham");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"OTA",null,"ota","ota","Turkish	 Ottoman (1500\xe2\x80\x93"."1928);","\xd9\x84\xd8\xb3\xd8\xa7\xd9\x86 \xd8\xb9\xd8\xab\xd9\x85\xd8\xa7\xd9\x86\xd9\x89");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"OTW",null,null ,"otw","Ottawa","Nishnaabemwin (Daawaamwin);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PAG",null,"pag","pag","Pangasinan","Pangasin\xc3\xa1n");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PAM",null,"pam","pam","Pampanga","Kapampangan");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PAP",null,"pap","pap","Papiamento","Papiamentu");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PAU",null,"pau","pau","Palauan","Tekoi ra Belau");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PCD",null,null ,"pcd","Picard","Picard");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PDC",null,null ,"pdc","German	 Pennsylvania","Pennsilfaani-Deitsch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PDT",null,null ,"pdt","Plautdietsch","Plautdietsch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PES",null,null ,"pes","Western Farsi","\xd9\x81\xd8\xa7\xd8\xb1\xd8\xb3\xdb\x8c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PFL",null,null ,"pfl","Pfaelzisch","P\xc3\xa4lzisch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PIH",null,null ,"pih","Pitcairn-Norfolk","Norfuk");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PMS",null,null ,"pms","Piedmontese","Piemont\xc3\xa8is");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PNT",null,null ,"pnt","Pontic","\xce\xa0\xce\xbf\xce\xbd\xcf\x84\xce\xb9\xce\xb1\xce\xba\xce\xac");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"POT",null,null ,"pot","Potawatomi","Neshnab\xc3\xa9mwen (Bod\xc3\xa9wadmimwen);");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PPI",null,null ,"ppi","Paipai","Aka'ala");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PQM",null,null ,"pqm","Malecite-Passamaquoddy","Peskotomuhkati");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PRD",null,null ,"prd","Dari (Persian);","(\xd9\x81\xd8\xa7\xd8\xb1\xd8\xb3\xdb\x8c (\xd8\xaf\xd8\xb1\xdb\x8c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PRG",null,null ,"prg","Prussian","Pr\xc5\xabsiska");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PRP",null,null ,"prp","Persian","\xd9\x81\xd8\xa7\xd8\xb1\xd8\xb3\xdb\x8c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"PRS",null,null ,"prs","Persian (Dari);","(\xd9\x81\xd8\xa7\xd8\xb1\xd8\xb3\xdb\x8c (\xd8\xaf\xd8\xb1\xdb\x8c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"QTZ",null,null ,"qtz","Reserved for local use.","\xe2\x80\x94");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"QUC",null,null ,"quc","Quich\xc3\xa9	 Central","Q'ich\xc3\xa9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"QXQ",null,null ,"qxq","Qashqa'i","Qa\xc5\x9fqayc\xc9\x99");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RAP",null,"rap","rap","Rapanui","Rapanui");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RAR",null,"rar","rar","Rarotongan","M\xc4\x81ori K\xc5\xabki '\xc4\x80irani");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RCF",null,null ,"rcf","R\xc3\xa9union Creole French","Kr\xc3\xa9ol R\xc3\xa9nion\xc3\xa9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RGN",null,null ,"rgn","Romagnol","Rumagn\xc3\xb2l");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RME",null,null ,"rme","Angloromani","Romanichal");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RMF",null,null ,"rmf","Romani	 Kalo Finnish","Roman\xc3\xb3 Kal\xc3\xb3");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RMO",null,null ,"rmo","Romani	 Sinte","Sinto");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ROM",null,"rom","rom","Romany","\xe0\xa4\xb0\xe0\xa5\x8b\xe0\xa4\xae\xe0\xa4\xbe\xe0\xa4\xa8\xe0\xa5\x8b");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RUE",null,null ,"rue","Rusyn","\xd1\x80\xd1\x83\xd1\x81\xd0\xb8\xd0\xbd");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RUO",null,null ,"ruo","Romanian	 Istro","Istrorom\xc3\xa5n\xc4\x83");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RUP",null,"rup","rup","Aromanian","Arm\xc4\x83neashce");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RUQ",null,null ,"ruq","Romanian	 Megleno","Meglenoroman\xc4\x83");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"RYU",null,null ,"ryu","Okinawan	 Central","\xe3\x81\x86\xe3\x81\xa1\xe3\x81\xaa\xe3\x83\xbc\xe3\x81\x90\xe3\x81\xa1");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SAH",null,"sah","sah","Sakha","\xd0\xa1\xd0\xb0\xd1\x85\xd0\xb0");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SAM",null,"sam","sam","Aramaic	 Samaritan","\xdc\x90\xdc\xaa\xdc\xa1\xdc\x9d\xdc\x90");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SAT",null,"sat","sat","Santali","\xe0\xa4\xb8\xe0\xa4\x82\xe0\xa4\xa5\xe0\xa4\xbe\xe0\xa4\xb2\xe0\xa5\x80");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SCN",null,"scn","scn","Sicilian","Sicilianu");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SCO",null,"sco","sco","Scots","Scots");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SCS",null,null ,"scs","Slavey	 North","Saht\xc3\xba Got\xe2\x80\x99ine");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SDC",null,null ,"sdc","Sardinian	 Sassarese","Sassaresu");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SEC",null,null ,"sec","Sechelt","Shashishalhem");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SEE",null,null ,"see","Seneca","On\xc3\xb5tow\xc3\xa1ka");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SEI",null,null ,"sei","Seri","Cmiique Iitom");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SEK",null,null ,"sek","Sekani","Tsek\xe2\x80\x99"."ehne");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SEL",null,"sel","sel","Selkup","\xd1\x88\xd3\xa7\xd0\xbb\xd1\x8c\xd3\x84\xd1\x83\xd0\xbc\xd1\x8b\xd1\x82");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SHH",null,null ,"shh","Shoshoni","Sosoni'");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SHI",null,null ,"shi","Tachelhit","\xd8\xaa\xd8\xb4\xd9\x84\xd8\xad\xd9\x8a\xd8\xaa");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SHS",null,null ,"shs","Shuswap","Secwepemctsin");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SHY",null,null ,"shy","Tachawit","Tachawit");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SID",null,"sid","sid","Sidamo","Sid\xc3\xa1mo");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SJD",null,null ,"sjd","Sami	 Kildin","\xd1\x81\xd0\xb0\xd0\xbc\xd1\x8c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SJW",null,null ,"sjw","Shawnee","Shaawanwa\xca\xbc");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SLR",null,null ,"slr","Salar","Salar");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SMA",null,"sma","sma","Southern Sami","Saemi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SMJ",null,"smj","smj","Lule Sami","S\xc3\xa1mi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SMN",null,"smn","smn","Inari Sami","S\xc3\xa4\xc3\xa4mi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SMS",null,"sms","sms","Skolt Sami","S\xc3\xa4\xc3\xa4'm");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SNK",null,"snk","snk","Soninke","Soninkanxaane");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SRM",null,null ,"srm","Saramaccan","Saam\xc3\xa1ka");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SRN",null,"srn","srn","Sranan","Sranang Tongo");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"STO",null,null ,"sto","Stoney","Isga I\xca\xbc"."abi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"STQ",null,null ,"stq","Saterland Frisian","Seeltersk");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"STR",null,null ,"str","Salish	 Straits","X\xca\xb7sen\xc9\x99\xc4\x8dq\xc9\x99n");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SUX",null,"sux","sux","Sumerian","Eme-\xc4\x9dir");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SVA",null,null ,"sva","Svan","\xe1\x83\x9a\xe1\x83\xa3\xe1\x83\xa8\xe1\x83\x9c\xe1\x83\xa3");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SWB",null,null ,"swb","Comorian","\xd8\xb4\xd9\x90\xd9\x82\xd9\x8f\xd9\x85\xd9\x8f\xd8\xb1\xd9\x90");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SWG",null,null ,"swg","Swabian","Schw\xc3\xa4"."bisch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SWL",null,null ,"swl","Swedish Sign Language","svenskt teckenspr\xc3\xa5k");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SXU",null,null ,"sxu","Saxon	 Upper","S\xc3\xa4"."chsisch");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SYR",null,"syr","syr","Syriac","\xdc\xa3\xdc\x98\xdc\xaa\xdc\x9d\xdc\x90\xdc\x9d\xdc\x90");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"SZL",null,null ,"szl","Silesian","\xc5\x9al\xc5\xafnsko godka");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TAB",null,null ,"tab","Tabassaran","\xd1\x82\xd0\xb0\xd0\xb1\xd0\xb0\xd1\x81\xd0\xb0\xd1\x80\xd0\xb0\xd0\xbd");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TAQ",null,null ,"taq","Tamasheq","\xd8\xaa\xd9\x8e\xd9\x85\xd9\x8e\xd8\xa7\xd8\xb4\xd9\x8e\xd9\x82\xd9\x92");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TAR",null,null ,"tar","Tarahumara	 Central","Ral\xc3\xa1muli");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TBW",null,null ,"tbw","Tagbanwa","tabanawa");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TCX",null,null ,"tcx","Toda","\xe0\xae\xa4\xe0\xaf\x8b\xe0\xae\xa4\xe0\xae\xbe");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TCY",null,null ,"tcy","Tulu","\xe0\xb2\xa4\xe0\xb3\x81\xe0\xb2\xb3\xe0\xb3\x81");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TDD",null,null ,"tdd","Tai N\xc3\xbc"."a","\xe1\xa5\x96\xe1\xa5\xad\xe1\xa5\xb0\xe1\xa5\x96\xe1\xa5\xac\xe1\xa5\xb3\xe1\xa5\x91\xe1\xa5\xa8\xe1\xa5\x92\xe1\xa5\xb0");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TET",null,"tet","tet","Tetum","Tetun");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TGX",null,null ,"tgx","Tagish","T\xc4\x81gizi");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"THP",null,null ,"thp","Thompson","N\xc5\x82"."e\xca\xbckepmxcin");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"THT",null,null ,"tht","Tahltan","T\xc4\x81\xc5\x82t\xc4\x81n");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TIG",null,"tig","tig","Tigre","Tigr\xc3\xa9");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TLH",null,"tlh","tlh","Klingon; tlhIngan-Hol","TlhIngan Hol");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TLI",null,"tli","tli","Tlingit","Ling\xc3\xadt");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TLY",null,null ,"tly","Talysh","\xd1\x82\xd0\xbe\xd0\xbb\xd1\x8b\xd1\x88\xd3\x99");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TOG",null,"tog","tog","Tonga (Nyasa);","ChiTonga");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TPI",null,"tpi","tpi","Tok Pisin","Tok Pisin");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TPN",null,null ,"tpn","Tupinamb\xc3\xa1","Ab\xc3\xa1\xc3\xb1"."e'enga");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TRV",null,null ,"trv","Seediq","Kari Seediq");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TSI",null,"tsi","tsi","Tsimshian","Sm\xe2\x80\x99"."algyax\xcc\xa3");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TTQ",null,null ,"ttq","Tamajaq	 Tawallammat","\xd8\xaa\xd9\x8e\xd9\x85\xd9\x8e\xd8\xa7\xd8\xac\xd9\x90\xd9\x82\xd9\x92");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TTS",null,null ,"tts","Thai	 Northeastern","\xe0\xb8\xa0\xe0\xb8\xb2\xe0\xb8\xa9\xe0\xb8\xb2\xe0\xb8\xad\xe0\xb8\xb5\xe0\xb8\xaa\xe0\xb8\xb2\xe0\xb8\x99");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TTT",null,null ,"ttt","Tat	 Muslim","Tati 	 \xd1\x82\xd0\xb0\xd1\x82\xd0\xb8");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TUM",null,"tum","tum","Tumbuka","ChiTumbuka");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TUS",null,null ,"tus","Tuscarora","Skar\xc3\xb9\xe2\x88\x99r\xc4\x99\xe2\x80\x99");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TUV",null,null ,"tuv","Turkana","Ng'aturk(w);ana");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TVL",null,"tvl","tvl","Tuvalu","'gana Tuvalu");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TYV",null,"tyv","tyv","Tuvinian","\xd0\xa2\xd1\x8b\xd0\xb2\xd0\xb0");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TZM",null,null ,"tzm","Tamazight	 Central Atlas","\xe2\xb5\x9c\xe2\xb5\x8e\xe2\xb4\xb0\xe2\xb5\xa3\xe2\xb5\x89\xe2\xb5\x96\xe2\xb5\x9c");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"UBY",null,null ,"uby","Ubykh","At\xca\xb7"."a\xcf\x87\xc9\x99"."bza");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"UDI",null,null ,"udi","Udi","\xd1\x83\xd0\xb4\xd0\xb8\xd0\xbd");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"UDM",null,"udm","udm","Udmurt","\xd1\x83\xd0\xb4\xd0\xbc\xd1\x83\xd1\x80\xd1\x82");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"UUM",null,null ,"uum","Urum","\xd0\xa3\xd1\x80\xd1\x83\xd0\xbc");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"VEC",null,null ,"vec","Venetian","V\xc3\xa8neto");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"VEP",null,null ,"vep","Veps","Veps\xc3\xa4");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"VOR",null,null ,"vor","Voro","Voro");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"VOT",null,"vot","vot","Votic","Va\xc4\x8f\xc4\x8f"."a");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"VRO",null,null ,"vro","V\xc3\xb5ro","V\xc3\xb5ro");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"WAR",null,"war","war","Waray (Philippines);","Winaray");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"WIY",null,null ,"wiy","Wiyot","Wiyot");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"WUU",null,null ,"wuu","Wu Chinese","\xe5\x90\xb4\xe8\xaf\xad");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"WYM",null,null ,"wym","Wymysorys","Wymysi\xc3\xb6"."ery\xc5\x9b");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"XAL",null,"xal","xal","Kalmyk; Oirat","\xd1\x85\xd0\xb0\xd0\xbb\xd1\x8c\xd0\xbc\xd0\xb3");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"XMF",null,null ,"xmf","Mingrelian","\xe1\x83\x9b\xe1\x83\x90\xe1\x83\xa0\xe1\x83\x92\xe1\x83\x90\xe1\x83\x9a\xe1\x83\xa3\xe1\x83\xa0\xe1\x83\x98");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"XSL",null,null ,"xsl","Slavey	 South","\xe1\x91\x8c\xe1\x93\x80\xe1\x92\x90");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"XSV",null,null ,"xsv","Sudovian","S\xc5\xab"."daviskai");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"ZAI",null,null ,"zai","Zapotec	 Isthmus","Diidxaz\xc3\xa1'");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"YUE","yue","yue","yue","Cantonese","Cantonese","Cantonese");
        //Adding language that are not part of ISO-639
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HKK","hkk","hkk","hkk","Hokkien","Hokkien","Hokkien");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"TEO","teo","teo","teo","Teo Chew","Teo Chew","Teo Chew");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HNN","hnn","hnn","hnn","Hainanese","Hainanese","Hainanese");
        self::addLanguageToArrays($tmpArrKeyCode,$tmpArrThreeCodeT,$tmpArrThreeCodeB,$tmpArrVidiunName,"HAK","hak","hak","hak","Hakka","Hakka","Hakka");

        $result = "<?php\n\n".self::assignArrayToVar($tmpArrKeyCode ,'arrayISO639');
        $result .= self::assignArrayToVar($tmpArrThreeCodeT, 'arrayISO639_T');
        $result .= self::assignArrayToVar($tmpArrThreeCodeB, 'arrayISO639_B');
        $result .= self::assignArrayToVar($tmpArrVidiunName ,'arrayVidiunName');
        return $result;
    }

    private static function assignArrayToVar(&$array ,$varName )
    {
        $strArr = var_export($array,true);
        return "self::\$$varName"." = ".$strArr.";\n\n";
    }

}
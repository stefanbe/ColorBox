<?php
 
/***************************************************************
* 
*  ColorBox Galerie von stefanbe
* Erzeugt die ColorBox v1.4.3 Galerie mit Einstellungen
*
***************************************************************/

class ColorBox extends Plugin {
    var $para_def = array(0 => NULL,1 => NULL,"thumb" => false,"img" => false,"imgtitle" => NULL,"gal" => false,
            "iframe" => false,
            "inline" => false,
            "scrolling" => true,
            "picsperrow" => 4,
            "showonly" => NULL,
            "slideshow" => false,
            "slideshowSpeed" => "2500",
            "slideshowAuto" => false,
            "loop" => true,
            "transition" => "elastic",
            "speed" => "300",
            "height" => false,
            "width" => false,
            "initialWidth" => "600",
            "initialHeight" => "450",
            "innerWidth" => false,
            "innerHeight" => false,
            "maxWidth" => false,
            "maxHeight" => false,
            "opacity" => "0.9",
            # neu in 1.4.31
            "fadeOut" => "300",
            "closeButton" => true,
            "titleTooltip" => true,

            "returnFocus" => false,# org = true
            "scalePhotos" => true,
            "reposition" => true
        );
    var $para = array();
    var $para_script = array();

    function getDefaultSettings() {
        return array(
            "picsperrow" => "4",
            "cfgTxtThumbnail" => "nothing",
            "cfgTxtcolorbox" => "nothing",
            "theme" => "slimbox2",
            "maxWidth" => "",
            "maxHeight" => "",
            "transition" => "elastic",
            "speed" => "300",
            "fadeOut" => "300",
            "slideshowAuto" => "false",
            "slideshowSpeed" => "2500",
            "loop" => "true",
            "closeButton" => "true"
        );
    }

    function getContent($value) {
        if(!isset($GLOBALS['colorbox_id']))
            $GLOBALS['colorbox_id'] = 1;
        else
            $GLOBALS['colorbox_id']++;
        $inline_div = "";
        if(strpos($value,"inline_start=") !== false and strpos($value,"=inline_end") !== false) {
            $start = strpos($value,"inline_start=");
            $end = strpos($value,"=inline_end") + strlen("=inline_end") - $start;
            $search = substr($value,$start,$end);
            $value = str_replace($search,"inline=true",$value);
            $inline_div = substr($search,strlen("inline_start="),-(strlen("=inline_end")));
            $inline_div = str_replace(array(",","="),array("&#94;&#44;","&#94;&#61;"),$inline_div);
            $inline_div = '<div style="display:none"><div id="inlinecolorbox-'.$GLOBALS['colorbox_id'].'">'.$inline_div.'</div></div>';
            global $syntax;
            $syntax->content = str_replace(array("</body>","</BODY>"),$inline_div."\n</body>",$syntax->content);
        }
        $this->para_script = array();
        $para = $this->para_def;
        $para["picsperrow"] = $this->settings->get("picsperrow") ? $this->settings->get("picsperrow") : "4";

        $para["maxWidth"] = $this->settings->get("maxWidth") ? $this->settings->get("maxWidth") : false;

        $para["maxHeight"] = $this->settings->get("maxHeight") ? $this->settings->get("maxHeight") : false;
        $para["transition"] = $this->settings->get("transition") ? $this->settings->get("transition") : "elastic";
        $para["speed"] = $this->settings->get("speed") ? $this->settings->get("speed") : "300";
        $para["fadeOut"] = $this->settings->get("fadeOut") ? $this->settings->get("fadeOut") : "300";
        $para["slideshowAuto"] = ($this->settings->get("slideshowAuto") == "true") ? true : false;
        $para["slideshowSpeed"] = $this->settings->get("slideshowSpeed") ? $this->settings->get("slideshowSpeed") : "2500";
        $para["loop"] = ($this->settings->get("loop") == "true") ? true : false;
        $para["closeButton"] = ($this->settings->get("closeButton") == "true") ? true : false;

        $this->para = $this->makeUserParaArray($value,$para);
        unset($para);

        $s_src = 'src="'; $l_src = strlen($s_src);
        $s_title = 'imagesubtitle">'; $l_title = strlen($s_title);
        foreach($this->para as $key => $values) {
            if(is_string($values))
                $values = trim($values);
            if($key === 0) {
                if(strpos($values,$s_src) > 0)
                    $this->para["thumb"] = $values;
                elseif(file_exists(BASE_DIR.GALLERIES_DIR_NAME."/".$values))
                    $this->para["gal"] = $values;
                else
                    $this->para["thumb"] = $values;
                continue;
            }
            if($key === 1) {
                if(($tmp_src = strstr($values,$s_src)) !== false) {
                    if(($tmp_title = strstr($values,$s_title)) !== false) {
                        $this->para["imgtitle"] = ' title="'.substr($tmp_title,$l_title,(strpos($tmp_title,'</span>') - $l_title)).'"';
                    }
                    $this->para["img"] = substr($tmp_src,$l_src,(strpos($tmp_src,'"',$l_src) - $l_src));
                } elseif($values == "showonly")
                    $this->para["showonly"] = "";
                continue;
            }
            if(in_array($key,array("thumb","img","imgtitle","gal"))) {
                continue;
            } elseif($key == "iframe" and $values) {
                global $specialchars;
                $this->para["img"] = $specialchars->decodeProtectedChr($values);
                $this->para_script["iframe"] = true;
            } elseif($key == "inline" and $values) {
                $this->para["img"] = '#inlinecolorbox-'.$GLOBALS['colorbox_id'];
                $this->para_script["inline"] = true;
            } elseif($key == "showonly" and !is_null($values)) {
                $this->para["showonly"] = $values;
            } elseif($key == "picsperrow") {
                $this->para[$key] = $values;
            } elseif(in_array($key,array("slideshow","slideshowSpeed","slideshowAuto","loop","transition","speed","height","width","initialWidth","initialHeight","innerWidth","innerHeight","maxWidth","maxHeight","opacity","scrolling","fadeOut","closeButton","returnFocus","scalePhotos","reposition"))) {
                if($this->para_def[$key] !== $this->para[$key])
                    $this->para_script[$key] = $values;
                if($key == "returnFocus" or $key == "slideshowAuto")# or $key == "scrolling"
                    $this->para_script[$key] = $values;
            }
        }
        unset($this->para[0],$this->para[1]);
        $html = false;
        $jquery_id = "";
        global $language;
        // wenns nur ein Bild ist
        if($this->para["thumb"] !== false and $this->para["img"] !== false) {
            // return colorbox Bild Link
            $html = '<a href="'.$this->para["img"].'" class="colorboxlink imgcolorbox-'.$GLOBALS['colorbox_id'].'"'.$this->para["imgtitle"].'>'.$this->para["thumb"].'</a>';
            $jquery_id = '.imgcolorbox-'.$GLOBALS['colorbox_id'];
        } elseif($this->para["gal"] !== false) {
            $dir_gallery      = BASE_DIR.GALLERIES_DIR_NAME."/".$this->para["gal"]."/";
            $alldescriptions = false;
            // Bild Beschreibungen hohlen
            if(is_file($dir_gallery."/texte.conf.php"))
                $alldescriptions = new Properties($dir_gallery."texte.conf.php");
            // Galerieverzeichnis einlesen
            $picarray = $this->getColorBoxPicsAsArray($dir_gallery, array("jpg", "jpeg", "jpe", "gif", "png"));
            if(count($picarray) > 0) {
                // wenn sie nicht leer ist erstellen
                $html = $this->getColorBoxThumbnails($picarray, $alldescriptions);
                $jquery_id = '.galcolorbox-'.$GLOBALS['colorbox_id'].' a';
                $this->para_script["rel"] = 'galcolorbox-'.$GLOBALS['colorbox_id'];
            } else
                // wenn die Galerie leer ist
                return $language->getLanguageValue("message_galleryempty_0");
        }
        if($html !== false) {
            global $syntax;
            $syntax->insert_jquery_in_head('jquery');
            $syntax->insert_in_head($this->ColorBoxhead());
            $para = "";
            $html .= '<script type="text/javascript">'
                .'jQuery("'.$jquery_id.'").colorbox({';
                foreach($this->para_script as $key => $value) {
                    if($value === "true" or (is_bool($value) and $value))
                        $para .= $key.':true,';
                    elseif($value === "false" or (is_bool($value) and !$value))
                        $para .= $key.':false,';
                    else
                        $para .= $key.':"'.$value.'",';
                }
                $html .= trim($para,"\x2C").'});';
                if($this->settings->get("cfgTxtcolorbox") !== "nothing"
                        and $this->para["titleTooltip"] === "false") {
                    $html .= '$(function() {'
                        .'$("'.$jquery_id.'").each(function(){'
                            .'$(this).data("title",$(this).attr("title")).removeAttr("title");'
                        .'});'
                    .'});';
                }
                $html .= '</script>';

            return $html;
        }
        return '<span class="deadlink">'.$language->getlanguagevalue("plugin_error_value_1","ColorBox").'</span>';

    } // function getContent

    function ColorBoxhead() {
        global $CMS_CONF;
        $lang = substr($CMS_CONF->get("cmslanguage"),0,2);
        $theme = "example1";
        if($this->settings->get("theme"))
            $theme = $this->settings->get("theme");
        $colorbox = '<link type="text/css" rel="stylesheet" href="'.$this->PLUGIN_SELF_URL.'theme/'.$theme.'/colorbox.css" />'
            .'<script type="text/javascript" src="'.$this->PLUGIN_SELF_URL.'js/jquery.colorbox-min.js"></script>'
            .'<script type="text/javascript">var theme_url = "'.$this->PLUGIN_SELF_URL.'theme/'.$theme.'/";</script>';
        if(file_exists($this->PLUGIN_SELF_DIR.'js/i18n/jquery.colorbox-'.$lang.'.js'))
            $colorbox .= '<script type="text/javascript" src="'.$this->PLUGIN_SELF_URL.'js/i18n/jquery.colorbox-'.$lang.'.js"></script>';

        if(file_exists($this->PLUGIN_SELF_DIR.'theme/'.$theme.'/add.js'))
            $colorbox .= '<script type="text/javascript" src="'.$this->PLUGIN_SELF_URL.'theme/'.$theme.'/add.js"></script>';
        return $colorbox;
     }

    // ------------------------------------------------------------------------------
    // Thumbnails-HTML erzeugen
    // ------------------------------------------------------------------------------
    function getColorBoxThumbnails($picarray, $alldescriptions) {
        global $specialchars;
        global $language;

        $cfgTxtThumbnail = $this->settings->get("cfgTxtThumbnail");
        $cfgTxtcolorbox = $this->settings->get("cfgTxtcolorbox");
        $dir_gallery_url  = URL_BASE.GALLERIES_DIR_NAME."/".$this->para["gal"]."/";
        $dir_thumbs_url   = URL_BASE.GALLERIES_DIR_NAME."/".$this->para["gal"]."/".PREVIEW_DIR_NAME."/";

        $thumbs = '<div class="galcolorbox-'.$GLOBALS['colorbox_id'].'">';
        if (is_null($this->para["showonly"]))
            $thumbs .= '<table class="gallerytable"><tr>';

        $i = 0;
        for ($i = 0; $i < count($picarray); $i++) {
            // Bildbeschreibung, Alt-Text, Beschreibung colorbox holen

            // $description ist der Text unter Thumbnail ($cfgTxtThumbnail kann sein "title", "filename", "nothing")
            $description = "";
            if ($cfgTxtThumbnail == "title")
                $description = $this->getColorBoxCurrentDescription($picarray[$i],$picarray,$alldescriptions);
            if ($cfgTxtThumbnail == "filename") 
                $description = $specialchars->rebuildSpecialChars($language->getLanguageValue("alttext_image_1",$picarray[$i]),true,true);

            // $colorbox_description ist der Untertitel im colorbox-Fenster und der mouseover-Text beim Thumbnail
            // $cfgTxtcolorbox kann sein "title", "filename", "nothing"
            $colorbox_description = "";
            if ($cfgTxtcolorbox == "title")
                $colorbox_description =  $this->getColorBoxCurrentDescription($picarray[$i],$picarray,$alldescriptions);
            if ($cfgTxtcolorbox == "filename") 
                $colorbox_description = $specialchars->rebuildSpecialChars($language->getLanguageValue("alttext_image_1",$picarray[$i]),true,true);
            if ($colorbox_description == "&nbsp;")
                $colorbox_description = "";

            // img-alt-Text ist entweder der Titel lt. Galerie oder der Dateiname
            $alttext = $description;
            if ($alttext == "&nbsp;")
                $alttext = $specialchars->rebuildSpecialChars($language->getLanguageValue("alttext_image_1",$picarray[$i]),true,true);

            // Einzelvorschau oder Normale Auflistung der Thumbs?
            if (!is_null($this->para["showonly"])) {
                // erster Durchlauf
                if ($i == 0) {
                    if ($this->para["showonly"] == "") {
                        // erstes Galeriebild als Vorschau
                        $thumbs .= '<a href="'.$specialchars->replaceSpecialChars($dir_gallery_url.$picarray[$i],true).'" title="'. $colorbox_description.'" class="colorboxlink">';
                        $thumbs .= '<img src="'.$specialchars->replaceSpecialChars($dir_thumbs_url.$picarray[$i],true).'" alt="'.$alttext.'" class="thumbnail" />';
                        $thumbs .= "</a>";
                    } else {
                        // Bild aus mozilo Syntax als Vorschau oder Textlink
                        // oder beliebiger Textlink als Vorschau
                        $thumbs .= '<a href="'.$specialchars->replaceSpecialChars($dir_gallery_url.$picarray[$i],true).'" title="'.$colorbox_description.'" class="colorboxlink">';
                        $thumbs .= $this->para["showonly"];
                        $thumbs .= "</a>";
                    }
                } else { // (i==0)
                    // sonst nurmehr die <a href..> für colorbox-Fenster erzeugen
                    $thumbs .= '<a href="'.$specialchars->replaceSpecialChars($dir_gallery_url.$picarray[$i],true).'" title="'. $colorbox_description.'" class="colorboxlink" style="display:none;"></a>'."\n";
                }
            } else { // !is_null($singlePreview) - normale Darstellung
                if (($i > 0) && ($i % $this->para["picsperrow"] == 0)) // Neue Tabellenzeile aller picsperrow Zeichen
                    $thumbs .= "</tr>\n<tr>";
                $thumbs .= '<td class="gallerytd" style="width:'.floor(100 / $this->para["picsperrow"]).'%;">';
                $thumbs .= '<div class="gallerytd-div"><a href="'.$specialchars->replaceSpecialChars($dir_gallery_url.$picarray[$i],true).'" title="'. $colorbox_description.'" class="colorboxlink">';
                $thumbs .= '<img src="'.$specialchars->replaceSpecialChars($dir_thumbs_url.$picarray[$i],true).'" alt="'.$alttext.'" class="thumbnail" />';
                $thumbs .= "</a><br />";
                if($description != "&nbsp;")
                    $thumbs .= '<div class="description">'.$description.'</div>';
                $thumbs .= "</div></td>\n";
            }
        } // for ... picarray

        if (is_null($this->para["showonly"])) {
            while ($i % $this->para["picsperrow"] > 0) {
                $thumbs .= '<td class="gallerytd" style="width:'.floor(100 / $this->para["picsperrow"]).'%;">&nbsp;</td>'."\n";
                $i++;
            }
        $thumbs .= "</tr></table>\n";
        }
        $thumbs .= "</div>";
        if (!is_null($this->para["showonly"]))
            return $thumbs;
        return '<div class="embeddedgallery">' . $thumbs . '</div>';
    }

    // ------------------------------------------------------------------------------
    // Beschreibung zum aktuellen Bild auslesen
    // ------------------------------------------------------------------------------
    function getColorBoxCurrentDescription($picname,$picarray,$alldescriptions) {
        global $specialchars;

        if(!$alldescriptions)
            return "&nbsp;";
        // Keine Bilder im Galerieverzeichnis?
        if (count($picarray) == 0)
            return "&nbsp;";
        // Bildbeschreibung einlesen
        $description = $alldescriptions->get($picname);
        if(strlen($description) > 0) {
            return $specialchars->rebuildSpecialChars($description,false,true);
        } else {
            return "&nbsp;";
        }
    }

    // ------------------------------------------------------------------------------
    // Auslesen des übergebenen Galerieverzeichnisses, Rückgabe als Array
    // ------------------------------------------------------------------------------
    function getColorBoxPicsAsArray($dir, $filetypes) {
        $picarray = array();
        $currentdir = opendir($dir);
        // Alle Dateien des übergebenen Verzeichnisses einlesen...
        while ($file = readdir($currentdir)){
            if(isValidDirOrFile($file) and (in_array(strtolower(substr(strrchr($file, "."), 1, strlen(strrchr($file, "."))-1)), $filetypes))) {
                // ... wenn alles passt, ans Bilder-Array anhängen
                $picarray[] = $file;
            }
        }
        closedir($currentdir);
        sort($picarray);
        return $picarray;
    }

//***************************************************************/
    function getConfig() {
        // Rückgabe-Array initialisieren
        // Das muß auf jeden Fall geschehen!
        $config = array();
        // Nicht vergessen: Das gesamte Array zurückgeben
        $config['picsperrow'] = array(
            "type" => "text",
            "maxlength" => "2",
            "size" => "3",
            "description" => $this->admin_lang->getLanguageValue("picsperrow"),
            "regex" => "/^[1-9][0-9]?/",
            "regex_error" => $this->admin_lang->getLanguageValue("picsperrow_error")
        );

        $config['cfgTxtThumbnail'] = array(
            "type" => "radio",
            "description" => $this->admin_lang->getLanguageValue("cfgTxtThumbnail"),
            "descriptions" => array(
                "title" => $this->admin_lang->getLanguageValue("title"),
                "filename" => $this->admin_lang->getLanguageValue("filename"),
                "nothing" => $this->admin_lang->getLanguageValue("nothing")
                )
            );

        $config['cfgTxtcolorbox'] = array(
            "type" => "radio",
            "description" => $this->admin_lang->getLanguageValue("cfgTxtcolorbox"),
            "descriptions" => array(
                "title" => $this->admin_lang->getLanguageValue("title"),
                "filename" => $this->admin_lang->getLanguageValue("filename"),
                "nothing" => $this->admin_lang->getLanguageValue("nothing")
                )
            );
 
        $theme = array();
        foreach(getDirAsArray(PLUGIN_DIR_REL.'/ColorBox/theme',"dir") as $value) {
            $theme[$value] = $value;
        }
        $config['theme'] = array(
            "type" => "select",
            "description" => $this->admin_lang->getLanguageValue("theme"),
            "descriptions" => $theme,
            "multiple" => "false"
            );
        $config['maxWidth'] = array(
            "type" => "text",
            "maxlength" => "8",
            "size" => "5",
            "description" => $this->admin_lang->getLanguageValue("maxWidth"),
            "regex" => "/^(\d+(%)?){1}$/",
            "regex_error" => $this->admin_lang->getLanguageValue("emptydigit_error")
        );
        $config['maxHeight'] = array(
            "type" => "text",
            "maxlength" => "8",
            "size" => "5",
            "description" => $this->admin_lang->getLanguageValue("maxHeight"),
            "regex" => "/^(\d+(%)?){1}$/",
            "regex_error" => $this->admin_lang->getLanguageValue("emptydigit_error")
        );
        $config['transition'] = array(
            "type" => "select",
            "description" => $this->admin_lang->getLanguageValue("transition"),
            "descriptions" => array(
                "none" => $this->admin_lang->getLanguageValue("transition_none"),
                "elastic" => $this->admin_lang->getLanguageValue("transition_elastic"),
                "fade" => $this->admin_lang->getLanguageValue("transition_fade")),
            "multiple" => "false"
            );
        $config['speed'] = array(
            "type" => "text",
            "maxlength" => "8",
            "size" => "5",
            "description" => $this->admin_lang->getLanguageValue("speed"),
            "regex" => "/^\d+$/",
            "regex_error" => $this->admin_lang->getLanguageValue("digit_error")
        );
        $config['fadeOut'] = array(
            "type" => "text",
            "maxlength" => "8",
            "size" => "5",
            "description" => $this->admin_lang->getLanguageValue("fadeOut"),
            "regex" => "/^\d+$/",
            "regex_error" => $this->admin_lang->getLanguageValue("digit_error")
        );
        $config['slideshowAuto'] = array(
            "type" => "checkbox",
            "description" => $this->admin_lang->getLanguageValue("slideshowAuto")
        );
        $config['slideshowSpeed'] = array(
            "type" => "text",
            "maxlength" => "8",
            "size" => "5",
            "description" => $this->admin_lang->getLanguageValue("slideshowSpeed"),
            "regex" => "/^\d+$/",
            "regex_error" => $this->admin_lang->getLanguageValue("digit_error")
        );
        $config['loop'] = array(
            "type" => "checkbox",
            "description" => $this->admin_lang->getLanguageValue("loop")
        );
        $config['closeButton'] = array(
            "type" => "checkbox",
            "description" => $this->admin_lang->getLanguageValue("closeButton")
        );
        return $config;
    }

    function getInfo() {
        global $ADMIN_CONF;
        $this->admin_lang = new Language($this->PLUGIN_SELF_DIR."lang/admin_".$ADMIN_CONF->get("language").".txt");

        if(false === ($info_htm = @file_get_contents($this->PLUGIN_SELF_DIR."lang/info_".$ADMIN_CONF->get("language").".html"))
                and false === ($info_htm = @file_get_contents($this->PLUGIN_SELF_DIR."lang/info_deDE.html")))
            $info_htm = $this->admin_lang->getLanguageValue("info_error");

        $info = array(
            // Plugin-Name
            "<b>ColorBox</b> Revision: 11",
            // Plugin-Version
            "2.0",
            // Kurzbeschreibung
            $info_htm,
            // Name des Autors
            "stefanbe",
            // Download-URL
            array("http://www.mozilo.de/forum/index.php?action=media","Templates und Plugins"),
            array('{ColorBox|}' => $this->admin_lang->getLanguageValue("info_description"))
            );
        return $info;
    }
}
?>
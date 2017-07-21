<?php

// Only add map icon above posts and pages
add_action( 'admin_head', 'add_map_button' );
function add_map_button() {

    if ( get_user_option('rich_editing') != 'true')
        return;

    add_action( 'media_buttons', 'add_map_icon' );
    add_action( 'admin_footer', 'add_map_popup' );
}

// Add button above editor if not editing map
function add_map_icon() {
    global $ytpl_popup_id;
    echo '<style>
	#add-map .dashicons {
		color: #888;
		margin: 0 4px 0 0;
		vertical-align: text-top;
		height: 18px;
        width: 18px;

		background-image: url(/wp-content/plugins/wp-miniaudioplayer/inc/maplayerbutton.svg);
		background-repeat: no-repeat;
    }
	#add-map {
		padding-left: 0.4em;
	}

	#add-map.disabled {
	    pointer-events:none;
		padding-left: 0.4em;
	}

	</style>
	<a id="add-map" class="button disabled" title="' . __("miniAudioPlayer", 'wpmbmap' ) . '" href="#" onclick="show_map_editor();">
		<div class="dashicons"></div>' . __("miniAudioPlayer", "wpmbmap") . '</a>';
}

class map_check_href
{
    function __construct() {

        add_filter('mce_external_plugins', array(&$this, 'add_map_tinymce_plugin'));
        add_filter('tiny_mce_before_init', array( &$this, 'add_map_TinyMCE_css' ) );
    }

    //include the tinymce javascript plugin
    function add_map_tinymce_plugin($plugin_array) {
        $plugin_array['wpmbmap'] =   plugins_url('map_short_code.js?_=' . MINIAUDIOPLAYER_VERSION, __FILE__);
        return $plugin_array;
    }
    //include the css file to style the graphic that replaces the shortcode
    function add_map_TinyMCE_css($in)
    {
        $in['content_css'] .= ",". plugins_url('map_short_code.css', __FILE__);;
        return $in;
    }
}
add_action("init", create_function('', 'new map_check_href();'));


$custom_player_id = "map_" . rand();

// Displays the lightbox popup to insert a YTPlayer shortcode to a post/page
function add_map_popup() {

    $exclude_class = get_option('miniAudioPlayer_excluded');
    $showVolumeLevel = get_option('miniAudioPlayer_showVolumeLevel');
    $allowMute = get_option('miniAudioPlayer_allowMute');
    $showTime = get_option('miniAudioPlayer_showTime');
    $showRew = get_option('miniAudioPlayer_showRew');
    $width = get_option('miniAudioPlayer_width');
    $skin = get_option('miniAudioPlayer_skin');
    $miniAudioPlayer_animate = get_option('miniAudioPlayer_animate');
    $miniAudioPlayer_add_gradient = get_option('miniAudioPlayer_add_gradient');
    $volume = get_option('miniAudioPlayer_volume');
    $downloadable = get_option('miniAudioPlayer_download');
    $custom_skin_name = get_option('miniAudioPlayer_custom_skin_name');
    $downloadable_security = get_option('miniAudioPlayer_download_security');

    ?>
    <div id="map-form" style="display: none;">
    <style>

        #map-form {
            position: fixed;
            width: 100%;
            min-width: 500px;
            height: 100%;
            top:0;
            bottom: 0;
            left: 0;
            right: 0;
            margin: auto;
            background: rgba(0,0,0,0.7);
            z-index: 100101;
            box-sizing: border-box;
            overflow: hidden;
        }

        #map-form header {
            position: absolute;
            background: #0073aa;
            color: #FFFFFF;
            height: 50px;
            box-sizing: border-box;
            margin: 0;
            top: 0;
            width: 100%;
            padding: 10px;
            box-shadow: 1px 4px 8px 0px rgba(0,0,0,0.3);
            z-index: 1000;
        }

        #map-form header h2 {
            color: #ffffff;
            margin: 0;
            line-height: 40px;
        }

        #map-form #editor {
            position: absolute;
            width: 50%;
            min-width: 700px;
            height: 90%;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            margin: auto;
            background: #FFFFFF;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            box-sizing: border-box;
        }

        #map-form #editor form {
            position: absolute;
            width: 100%;
            top: 50px;
            left: 0;
            height: calc(100% - 55px);
            overflow: auto;
            padding: 10px;
            box-sizing: border-box;
        }

        #map-form fieldset {
            font-size: 16px;
            border: none;
            font-family: inherit;
            font-family: Helvetica Neue, Arial, Helvetica, sans-serif;
        }

        #map-form fieldset span.label {
            display: inline-block;
            width: 45%;
            font-size: 100%;
            font-weight: 400;
            vertical-align: top;
        }

        #map-form fieldset div {
            margin: 0;
            padding: 9px!important;
            display: block;
            font-size: 16px;
            border-bottom: 1px dotted #cccccc;
        }

        #map-form input, textarea, select {
            font-size: 100%;
        }

        #map-form input[type=text], textarea {
            width: 54%;
        }

        #map-form .sub-set {
            background: #f3f3f3;
        }

        #map-form .media-modal-close .media-modal-icon:before {
            color: #FFFFFF;
        }

        #map-form .actions {
            text-align: right;
            padding: 10px;
            background: rgba(158, 158, 158, 0.19);
        }

        .help-inline {
            font-size: 16px;
            font-weight: 300;
            display: block;
            color: #999;
            padding-left: 0;
            margin: 5px 0;
        }

        .help-inline.inline {
            display: inline-block;
            font-weight: 400;
            padding-left: 10px;
        }


    </style>

    <div id="editor">
        <header>
            <h2><?php _e('mb.miniAudioPlayer editor', 'wpmbmap'); ?></h2>
            <button onclick="hide_map_editor()" type="button" class="button-link media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text">Close panel</span></span></button>
        </header>

        <form id="map_form" action="#">
            <div class="actions">
                <input type="submit" value="Insert code" class="button-primary"/>
            </div>

            <fieldset>
                <div>
                    <span class="label"><?php _e('Don’t render', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="checkbox" name="exclude" value="true"/>
                    <span class="help-inline"><?php _e('check to exclude this link', 'wp-miniaudioplayer'); ?> (<?php echo $exclude_class ?>)</span>
                </div>
                <div>
                    <span class="label"><?php _e('Audio url', 'wp-miniaudioplayer'); ?> <span style="color:red">*</span> : </span>
                    <input type="text" name="url" class="span5"/>
                    <span class="help-inline"><?php _e('A valid .mp3 url', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Audio title', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="text" name="audiotitle" class="span5"/>
                    <span class="help-inline"><?php _e('The audio title', 'wp-miniaudioplayer'); ?></span><br>
                    <span class="label"> </span>
                    <button class="button" id="metadata" onclick="getFromMetatags();jQuery(this).hide(); return false" style="color: gray" ><?php _e('Get the title from meta-data', 'wp-miniaudioplayer'); ?></button>
                </div>

                <div>
                    <span class="label"><?php _e('Skin', 'wp-miniaudioplayer'); ?>:</span>
                    <select name="skin">
                        <option value="black"><?php _e('black', 'wp-miniaudioplayer'); ?></option>
                        <option value="blue"><?php _e('blue', 'wp-miniaudioplayer'); ?></option>
                        <option value="orange"><?php _e('orange', 'wp-miniaudioplayer'); ?></option>
                        <option value="red"><?php _e('red', 'wp-miniaudioplayer'); ?></option>
                        <option value="gray"><?php _e('gray', 'wp-miniaudioplayer'); ?></option>
                        <option value="green"><?php _e('green', 'wp-miniaudioplayer'); ?></option>
                        <option value="<?php echo $custom_skin_name ?>"><?php echo $custom_skin_name ?></option>
                    </select>
                    <span class="help-inline"><?php _e('Set the skin color for the player', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Gradient', 'wp-miniaudioplayer'); ?>:</span>
                    <input type="checkbox" name="addGradientOverlay" value="true"/>
                    <span class="help-inline"><?php  _e('Check to add a gradient to the player skin', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Animate', 'wp-miniaudioplayer'); ?>:</span>
                    <input type="checkbox" name="animate" value="true"/>
                    <span class="help-inline"><?php _e('Check to activate the opening / closing animation', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Width', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="text" name="width" class="span6"/>
                    <span class="help-inline"><?php _e('Set the player width', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Volume', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="text" name="volume" class="span6"/>
                    <span class="help-inline"><?php _e('(from 1 to 10) Set the player initial volume', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Autoplay', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="checkbox" name="autoplay" value="true"/>
                    <span class="help-inline"><?php _e('Check to start playing on page load', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Loop', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="checkbox" name="loop" value="false"/>
                    <span class="help-inline"><?php _e('Check to loop the sound', 'wp-miniaudioplayer'); ?></span>
                </div>

                <h2><?php _e('Show/Hide', 'wp-miniaudioplayer'); ?></h2>

                <div>
                    <span class="label"><?php _e('Volume control', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="checkbox" name="showVolumeLevel" value="true"/>
                    <span class="help-inline"><?php _e('Check to show the volume control', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Time control', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="checkbox" name="showTime" value="true"/>
                    <span class="help-inline"><?php _e('Check to show the time control', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Mute control', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="checkbox" name="allowMute" value="true"/>
                    <span class="help-inline"><?php _e('Check to activate the mute button', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Rewind control', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="checkbox" name="showRew" value="true"/>
                    <span class="help-inline"><?php _e('Check to show the rewind control', 'wp-miniaudioplayer'); ?></span>
                </div>

                <div>
                    <span class="label"><?php _e('Downloadable', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="checkbox" name="downloadable" value="false" onclick="manageSecurity(this)"/>
                    <span class="help-inline"><?php _e('Check to show the download button', 'wp-miniaudioplayer'); ?></span><br>
                </div>

                <div>
                    <span class="label" style="font-weight: normal; color: gray"><?php _e('Only registered', 'wp-miniaudioplayer'); ?>: </span>
                    <input type="checkbox" name="downloadable_security" value="true"/>
                    <span class="help-inline"><?php _e('Check to limit downloads to registered users', 'wp-miniaudioplayer'); ?></span>
                </div>

                <script>
                    function manageSecurity(el){

                        var security = jQuery('[name=downloadablesecurity]');
                        if(jQuery(el).is(":checked")){
                            security.removeAttr('disabled');
                        }else{
                            security.attr('disabled','disabled');
                            security.removeAttr('checked');
                        }
                    }
                </script>

            </fieldset>

            <div class="actions">
                <input type="submit" value="Insert code" class="button-primary"/>
                <input class="button" type="reset" value="Reset settings"/>

            </div>

        </form>
    </div>
    </div>

    <script>

    var selection = null;
    var tmpInfo = {};

    jQuery(function(){
        jQuery(".wp-editor-tabs button").on("click.map", function(){

            setTimeout(function(){
                if(!tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden() ){
                    jQuery("#add-map").css("opacity",.5);
                } else {
                    jQuery("#add-map").css("opacity",1);
                }
            },400)
        })
    });

    function getFromMetatags(){
        if (typeof ID3 == "object") {
            ID3.loadTags(document.audioURL, function () {
                var info = {};
                info.title = ID3.getTag(document.audioURL, "title");
                info.artist = ID3.getTag(document.audioURL, "artist");
                info.album = ID3.getTag(document.audioURL, "album");
                info.track = ID3.getTag(document.audioURL, "track");
                info.size = ID3.getTag(document.audioURL, "size");
                if(info.title && info.title!=undefined){
                    jQuery("[name='audiotitle']").val(info.title + " - " +info.artist);

                    tmpInfo = info;
                }else{
                    jquery("button#metadata").after("no meta-data available for this file");
                }
            })
        }
    }

    function show_map_editor(){

        if (tinyMCE.activeEditor == null || tinyMCE.activeEditor.isHidden() != false){
            alert("You should switch to the visual editor");
            return;
        }

        var map_editor = tinyMCE.activeEditor;

        var map_form = jQuery('#map-form form').get(0);

        var selection = map_editor.selection.getNode();
        map_editor.isValidURL = false;
        map_editor.isHref = false;

        if (jQuery(selection).is("a[href *= '.mp3']") || jQuery(selection).find("a[href *= '.mp3']").lenght>0 || jQuery(selection).prev().is("a[href *= '.mp3']")) {
            map_editor.isHref = true;
            map_editor.isValidURL = true;
        } else if(jQuery(selection).is("a") || jQuery(selection).find("a").lenght>0 || jQuery(selection).prev().is("a" )) {
            map_editor.isHref = true;
        }

        if(! map_editor.isHref){
            alert("Select a link to an mp3 file to customize the player.");
            return;
        }

        if(!map_editor.isValidURL){
            var d = confirm("the selected Link doesn't seams a valid MP3 path; do you want to continue anyway?");
            if (!d)
                return;

        }
        map_form.reset();

        jQuery("body").css({overflow:"hidden"});
        jQuery("#map-form").slideDown(300);

        selection = map_editor.selection.getNode();

        map_editor.selection.select(selection,true);

        var $selection = jQuery(selection);

        var map_element = $selection.find("a[href *= '.mp3']");
        if (map_element.length){
            selection = map_editor.selection.select(map_element.get(0),true);
        }else if($selection.prev().is("a[href *= '.mp3']")){
            selection = map_editor.selection.select($selection.prev().get(0),true);
        }

        $selection = jQuery(selection);

        var url = document.audioURL = $selection.attr("href");
        var title = $selection.html();
        var isExcluded = $selection.hasClass("<?php echo $exclude_class ?>");

        var metadata = $selection.metadata();

        if(metadata.volume)
            metadata.volume =  parseFloat(metadata.volume)*10;

        if(jQuery.isEmptyObject(metadata)){
            var defaultmeta = {
                showVolumeLevel:<?php echo empty($showVolumeLevel) ? false : $showVolumeLevel ?>,
                allowMute:<?php echo $allowMute ? "true" : "false"?>,
                showTime:<?php echo $showTime ? "true" : "false"?>,
                showRew:<?php echo $showRew ? "true" : "false"?>,
                width:"<?php echo $width ?>",
                skin:"<?php echo $skin ?>",
                animate:<?php echo $miniAudioPlayer_animate ? "true" : "false" ?>,
                loop:false,
                addGradientOverlay: <?php echo $miniAudioPlayer_add_gradient ? "true" : "false" ?>,
                downloadable:<?php echo $downloadable ? "true" : "false" ?>,
                downloadable_security:<?php echo $downloadable_security ? "true" : "false" ?>,
                volume:parseFloat(<?php echo $volume ?>)*10
            };
            jQuery.extend(metadata,defaultmeta);
        }

        jQuery.extend(metadata, {exclude:isExcluded});

        jQuery("[name='url']", map_form).val(url);

        jQuery("[name='audiotitle']", map_form).val(title);

        for (var i in metadata){

//                console.debug(i, metadata[i]);

            if(typeof metadata[i] == "boolean"){
                if(eval(metadata[i]) == true)
                    jQuery("[name="+i+"]").attr("checked",  "checked");
                else
                    jQuery("[name="+i+"]").removeAttr("checked");

            }else
                jQuery("[name="+i+"]").val(metadata[i]);
        }

        var map_form = jQuery('#map-form form').get(0)


        map_form.onsubmit = insertCode;

    }

    function insertCode(e){

        var map_editor = tinyMCE.activeEditor;
        var map_form = jQuery('#map-form form').get(0)

        var map_params = "{";
        if(jQuery("[name='skin']", map_form).val().length>0)
            map_params+="skin:'"+jQuery("[name='skin']").val()+"', ";
        map_params+="animate:"+(jQuery("[name='animate']").is(":checked") ? "true" : "false")+", ";
        if(jQuery("[name='width']", map_form).val().length>0)
            map_params+="width:'"+jQuery("[name='width']", map_form).val()+"', ";
        if(jQuery("[name='volume']", map_form).val().length>0)
            map_params+="volume:"+ jQuery("[name='volume']", map_form).val()/10 +", ";
        map_params+="autoplay:"+(jQuery("[name='autoplay']", map_form).is(":checked") ? "true" : "false")+", ";
        map_params+="loop:"+(jQuery("[name='loop']", map_form).is(":checked") ? "true" : "false")+", ";
        map_params+="showVolumeLevel:"+(jQuery("[name='showVolumeLevel']", map_form).is(":checked") ? "true" : "false")+", ";
        map_params+="showTime:"+(jQuery("[name='showTime']", map_form).is(":checked") ? "true" : "false")+", ";
        map_params+="allowMute:"+(jQuery("[name='allowMute']", map_form).is(":checked") ? "true" : "false")+", ";
        map_params+="showRew:"+(jQuery("[name='showRew']", map_form).is(":checked") ? "true" : "false")+", ";
        map_params+="addGradientOverlay:"+(jQuery("[name='addGradientOverlay']", map_form).is(":checked") ? "true" : "false")+", ";
        map_params+="downloadable:"+(jQuery("[name='downloadable']", map_form).is(":checked") ? "true" : "false")+", ";
        map_params+="downloadablesecurity:"+(jQuery("[name='downloadablesecurity']", map_form).is(":checked") ? "true" : "false")+", ";
        map_params+="id3: false";
        map_params+="}";
        map_params = map_params.replace(", }", "}");

        var isExcluded = jQuery("[name='exclude']", map_form).is(":checked") ? "<?php echo $exclude_class ?> " : "";

        var map_a = "<a id='mbmaplayer_"+new Date().getTime()+"' class=";
        map_a += "\"mb_map " + isExcluded + map_params + "\" ";


        for (var x in tmpInfo){
            map_a += "meta-"+ x +"=\""+tmpInfo[x]+"\" ";
        }
        map_a += "href=\""+jQuery("[name='url']", map_form).val()+"\">";
        map_a+=jQuery("[name='audiotitle']", map_form).val();
        map_a+="</a>";
        map_editor.execCommand('mceInsertContent', 0, map_a);

        hide_map_editor();

        return false;
    };


    function hide_map_editor(){
        jQuery("#map-form").slideUp(300);
        jQuery("body").css({overflow:"auto"});
    }

    jQuery("body").on("click","#map-form", function(e) {
        var target = e.originalEvent.target;
        if(jQuery(target).parents().is("#map-form"))
            return;
        hide_map_editor();
    });

    </script>

<?php

}

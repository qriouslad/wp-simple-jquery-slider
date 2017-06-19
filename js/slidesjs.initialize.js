jQuery(function() {
      jQuery('#slides').slidesjs({
        width: 940,
        height: 528,
        navigation: false,
        play: {
        	active: setting.playBtn,
        	effect: setting.effect,
        	auto: setting.autoplay,
        	interval: setting.interval,
        },
      });
    });


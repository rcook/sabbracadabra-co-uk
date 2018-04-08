var websitename = { src: flashRootLocation + 'Site/logo/black.swf' };

sIFR.useStyleCheck = true;

sIFR.activate(websitename);

if (typeof(homePage) !== 'undefined' && homePage == true) {
				sIFR.replace(websitename, {
				  selector: '#textlogo',
				  css: '.sIFR-root a { background:transparent; color: #9B0400; padding:10px;  font-size:55px; cursor:pointer; font-weight:normal; text-decoration:none; text-align:left; text-indent:10px;}.sIFR-root a:hover {  color: #4A0203;}.sIFR-root { background:transparent; color: #9B0400; padding:10px;  font-size:70px; cursor:pointer; font-weight:bold; text-decoration:none; text-align:left; text-indent:10px;}',
				  wmode: 'transparent',
				  filters: {
				  
				  	BevelFilter: {
					  type:'inner',
					  blurY:2, 
					  blurX:2, 
					  knockout:false, 
					  strength:2.3, 
					  quality:3, 
					  shadowAlpha:1, 
					  shadowColor:0xffffff, 
					  highlightAlpha:1, 
					  highlightColor:0x680701, 
					  angle:90, 
					  distance:2
				 	  },
				
					GlowFilter: {	  
					  strength:1.35, 
					  blurY:9, 
					  blurX:9, 
					  knockout:false, 
					  inner:true, 
					  quality:3, 
					  alpha:1, 
					  color:'#4A0203'
				      },
				    DropShadowFilter: {
					  hideObject:false, 
					  strength:0.85,
				 	  blurY:4, 
				 	  blurX:4, 
					  knockout:false, 
				 	  inner:true, 
				 	  quality:10, 
				 	  alpha:.75, 
				 	  color:'#4A0203',
				 	  angle:135, 
				 	  distance:10
					  }
				  
				  }
				
				});

} else {
				sIFR.replace(websitename, {
				  selector: '#textlogo',
				  css: '.sIFR-root a { background:transparent; color: #A88545; padding:10px;  font-size:55px; cursor:pointer; font-weight:normal; text-decoration:none; text-align:left; text-indent:10px;}.sIFR-root a:hover {  color: #7B6233;}.sIFR-root { background:transparent; color: #A88545; padding:10px;  font-size:70px; cursor:pointer; font-weight:bold; text-decoration:none; text-align:left; text-indent:10px;}',
				  wmode: 'transparent',
				  filters: {
				  
				  	BevelFilter: {
					  type:'inner',
					  blurY:2, 
					  blurX:2, 
					  knockout:false, 
					  strength:2.3, 
					  quality:3, 
					  shadowAlpha:1, 
					  shadowColor:0xffffff, 
					  highlightAlpha:1, 
					  highlightColor:0x7B6233, 
					  angle:90, 
					  distance:2
				 	  },
				
					GlowFilter: {	  
					  strength:1.35, 
					  blurY:9, 
					  blurX:9, 
					  knockout:false, 
					  inner:true, 
					  quality:3, 
					  alpha:1, 
					  color:'#ffffff'
				      },
				    DropShadowFilter: {
					  hideObject:false, 
					  strength:0.85,
				 	  blurY:4, 
				 	  blurX:4, 
					  knockout:false, 
				 	  inner:true, 
				 	  quality:10, 
				 	  alpha:.75, 
				 	  color:'#ffffff',
				 	  angle:135, 
				 	  distance:10
					  }
				  
				  }
				
				});
}

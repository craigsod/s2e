FCKConfig.SkinPath = FCKConfig.BasePath + 'skins/default/' ;
FCKConfig.BaseHref = 'http://www.s2e-demo.com/';
FCKConfig.EditorAreaCSS = '/styleks1.css';
FCKConfig.StylesXmlPath = '/ezed_styles.xml' ;
FCKConfig.ImageDlgHideLink = true ;
FCKConfig.ImageDlgHideAdvanced = true ;
FCKConfig.ImageUpload = false ;
FCKConfig.FlashUpload = false;
FCKConfig.FormatOutput = false ;
FCKConfig.LinkUpload = true;
FCKConfig.LinkBrowserWindowWidth = '500' ;
FCKConfig.LinkBrowserWindowHeight = '400' ;
FCKConfig.LinkUploadDeniedExtensions = ".(7z|aiff|asf|avi|bmp|csv|doc|fla|flv|gif|gz|gzip|jpeg|jpg|mid|mov|mp3|mp4|mpc|mpeg|mpg|ods|odt|png|ppt|pxd|qt|ram|rar|rm|rmi|rmvb|rtf|sdc|sitd|swf|sxc|sxw|tar|tgz|tif|tiff|txt|vsd|wav|wma|wmv|xls|xml|zip)$" ;
FCKConfig.LinkUploadAllowedExtensions = ".(pdf)$" ;
FCKConfig.ImageBrowserURL = '/ezed_image_browser.php';
FCKConfig.LinkDlgHideAdvanced = true ;
FCKConfig.LinkDlgHideTarget = false ;
FCKConfig.DefaultLinkTarget = '_self' ;
FCKConfig.ToolbarCanCollapse = false;
FCKConfig.MaxUndoLevels = 25 ;
FCKConfig.CustomStyles = '';
FCKConfig.ForcePasteAsPlainText = true ;
FCKConfig.FontColors = 'FF5200,E50278,333333' ;
FCKConfig.EnableMoreFontColors = true;
FCKConfig.FontSizes = '8px/8;10px/10;12px/12;14px/14;18px/18;24px/24;36px/36;48px/48';
FCKConfig.ToolbarSets["Basic"] = [
['Cut','Copy','Paste'],
['Undo','Redo','-','Bold','Italic','TextColor','FontSize','-'],
['OrderedList','UnorderedList','-','Outdent','Indent'],
['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
'/',
['Style'],
['Table','Image','SpecialChar','-','Link','Unlink','Anchor','YouTube']
] ;

FCKConfig.ToolbarSets["Admin"] = [
['Cut','Copy','Paste'],
['Undo','Redo','-','Bold','Italic','TextColor','FontSize','-'],
['OrderedList','UnorderedList','-','Outdent','Indent'],
['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
'/',
['Style'],
['Table','Image','Flash','SpecialChar','Source','-','Link','Unlink','Anchor','YouTube']
] ;
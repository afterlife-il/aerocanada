<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  	
<script src="https://apis.google.com/js/client.js"></script>


<script type="text/javascript">
	  function auth() {
	    var config = {
	      'client_id': '828515983813-8516jlvb5q96pd3fpnimssa1fn6fbg5l.apps.googleusercontent.com',//your client ID HERE
	      'scope': 'https://www.google.com/m8/feeds'
	    };
	    gapi.auth.authorize(config, function() {
	      fetch(gapi.auth.getToken());  
	     
	    });
	  }
	
	  function fetch(token) {
	    $.ajax({
		    url: "https://www.google.com/m8/feeds/contacts/default/full?access_token=" + token.access_token + "&alt=json",
		    dataType: "jsonp",
		    success:function(data) {
                              // display all your data in console
		              console.log(JSON.stringify(data));
		    }
		});
	}	
</script>

<button onclick="auth();">GET CONTACTS FEED</button>
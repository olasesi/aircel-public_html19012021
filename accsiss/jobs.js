    function clearError(){
        const element = document.getElementById("noticebox");
        element.classList.remove("alert");
        element.innerHTML = "";
    }
    
    function displayError(error){
        const element = document.getElementById("noticebox");
        element.classList.add("alert");
        element.classList.add("alert-danger");
        element.innerHTML = error;
    }
	function validateForm(){
		var flag = 1;
		var mssg='';
		
		var semail = /^[\w]+(\.[\w]+)*@([\w]+\.)+[a-z]{2,7}$/i;
		if(document.getElementById('email').value.length < 1 || ! semail.test(document.getElementById('email').value)){
			mssg += 'Email is Invalid <br />';
			flag =0;
		}		
		if(document.getElementById('fullname').value.length < 1){
			mssg += 'full name is Invalid <br />';
			flag = 0;
		}
	
		if(document.getElementById('message').value.length < 1){
			mssg += 'You have not told us about yourself <br />';
			flag = 0;
		}		

		
	/*	var semail = /^[\w]+(\.[\w]+)*@([\w]+\.)+[a-z]{2,7}$/i;
		if(document.getElementById('email').value.length < 1 || ! semail.test(document.getElementById('email').value)){
			mssg += 'Email is Invalid \n\r';
			flag =0;
		}	*/
		if(flag === 0){
			displayError(mssg);
			return false;
		}else{ 
			return true; 
		}
	}

	getAjaxObject = function(){
		var requeste;
		try{
			requeste = new XMLHttpRequest();
		}catch(error){
			try{
				requeste = new ActiveXobject('Microsoft.XMLHTTP');
			}catch(error){
				return 'Error';
			}
		}
		return requeste;
	}

	function getParams(){
		var paramstr = new FormData();
	 
		paramstr.append('email',document.getElementById('email').value);
		paramstr.append('fullname',document.getElementById('fullname').value);
		paramstr.append('message',document.getElementById('message').value);
		paramstr.append('cvfilename',document.getElementById('cvfile').files[0].name);		
		paramstr.append('cvfile',document.getElementById('cvfile').files[0]);		

		return paramstr;

	}
	
	verifyJobs = function(e){
	    
	    if(!validateForm()){
	       return; 
	    }
		var request = getAjaxObject();
		//alert('' + request);
	    document.getElementById("message").innerHTML = "";
		request.open('POST','../../scripts/jobscript.php',true);
		request.onreadystatechange = function(){
		//alert(document.getElementById('username').value); 	
			
			if(request.readyState === 1){

			}
			if(request.readyState === 4 && request.status === 200){
				//alert(document.getElementById('username').value);
				//alert(request.responseText);
				
				if((request.responseText).trim() === "INVALID USER" || (request.responseText).trim() === "OTHERS" || (request.responseText).trim() === "ERROR"){
					displayError("Invalid values. Try again later");
				}else if( (request.responseText).trim() === "Use another email"){
				    displayError(request.responseText);
				}else{
				    if(request.responseText === "SUCCESS"){
				        window.location.href = "http://www.miratechnologiesng.com";
				    }
				    document.getElementById("noticebox").innerHTML = request.responseText;
					//alert('Welcome <i>' + request.responseText + '</i> ');

					//var cuser = document.getElementById('currentuser');
				    //cuser.value = request.responseText

					//var slo = document.getElementById('submitlogout');
					//slo.addEventListener('click',logoutUser,false);
				}
				//fs += request.responseText;
				
			}else{
					
			}

			e.stopPropagation();
		}

		//document.getElementById('sysfootpanel').innerHTML = fs;
		//request.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		//request.setRequestHeader('Content-length',params.length);
		request.setRequestHeader('Connection','close');
		request.send(getParams());	    
	}

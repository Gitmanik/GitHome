function refreshFrame(){    
    var timestamp = new Date().getTime();  
    var queryString = 'frame.php' + "?t=" + timestamp; 
    var imageElement = document.querySelector(".frame img");  
    console.log(`Requesting ${queryString}`);
    
    var downloadingImage = new Image();
    downloadingImage.onload = function(){
        console.log(`Downloaded ${this.src}`);
        imageElement.src = this.src;   
        refreshFrame();
    };

    downloadingImage.src = queryString;    
}

refreshFrame();
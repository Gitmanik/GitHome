const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

function GitHome_fetch(url, data = null, method = "GET")
{
    const formData = new FormData();
    
    if (data != null)
        for(const name in data)
            formData.append(name, obj[name]);
    
    if (method == "GET")
    {
        fetch(url, {
            method: "GET"
        }).then(() => {location.reload()});
    }
    else
    {
        fetch(url, {
            method: method,
            body: formData
        }).then(() => {location.reload()});
    }

}
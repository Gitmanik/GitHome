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
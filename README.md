# Citations From Barcodes

This script retrieve some useful library information given a library-supplied barcode.  
As of now it works with the University of Maryland-College Park's Aleph ILS.  

The formatted citations are generated using the Worldcat Basic API.  
You'll need a Worldcat Basic API Key.  
The key needs to be saved as an environment variable on your server. There are various ways to do that, most of which I know nothing about.
I use an Apache server using XAMPP or MAMP. For XAMPP/MAMP you create an .htaccess file in your htdocs directory.  
In the .htaccess file goes your API key, like this:
```
SetEnv HTTP_WORLDCAT_BASIC_KEY "your key here"
```
If you have other API keys you use this is a good place to keep them. One per line:
```
SetEnv HTTP_WORLDCAT_BASIC_KEY "your key here"  
SetEnv HTTP_GOODREADS_KEY "your key here"  
SetEnv HTTP_FLICKR_KEY "your key here"     
```

Note, the variable names need to begin with "HTTP\_" for security reasons.  

In Apache, you may also need to edit your httpd.conf file. Find it (in /xampp/apache/conf in XAMPP) and make sure the line 
```LoadModule rewrite_module modules/mod_rewrite.so```
is uncommented (delete the # at the beginning of the line if it's there).
Save the file and restart the server.

On MAMP, you may need to do some other configuration in the httpd.conf depending on your particular version. See for example:  
[http://trevordavis.net/blog/use-htaccess-files-with-mamp](http://trevordavis.net/blog/use-htaccess-files-with-mamp)  
[http://stackoverflow.com/questions/7670561/how-to-get-htaccess-to-work-on-mamp](http://stackoverflow.com/questions/7670561/how-to-get-htaccess-to-work-on-mamp)  

If you are using cloud hosting, there may be other ways you need to set the environment variables. I use Appfog. In AppFog, you can use the GUI interface, open your application, and click `environment variables` on the left. At least I think that works. Still need to test.


 

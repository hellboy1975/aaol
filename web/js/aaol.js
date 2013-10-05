//aaol.js

// IIFE - Immediately Invoked Function Expression
  (function(yourcode) {

    // The global jQuery object is passed as a parameter
  	yourcode(window.jQuery, window, document);

  }(function($, window, document) {

    // The $ is now locally scoped 

   // Listen for the jQuery ready event on the document
   $(function() {

      // The DOM is ready!
 
      // turn on the ckeditor
      $( 'textarea.html-editor' ).ckeditor();

     /**
      * /newpost
      *
      * When the post title changes, automatically update the slug (convert it to lowercase and replace space with -)
      */
      $('#form_title').change(function() {
          var title = $(this).val().toLowerCase().replace(/ /g,"-");;
          $('#form_slug').val(title);
      });
      

   });

   // The rest of the code goes here!

  
  }));



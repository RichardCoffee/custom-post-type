# Custom Post Type

This is a base class for WordPress custom post types.  This is -not- a UI, nor is it intended to be.  If you are looking for that, then try here: https://github.com/WebDevStudios/custom-post-type-ui

I have seen quite a few different ways of how people handle custom post types in wordpress, but was never really happy with any of them.  They all did what they were designed to, but never really seemed to cover the things that I needed.  So I set out to make my own.  It is still very much a work in progress.  Please drop me a note if you find it useful in your own projects.

The basis for a lot of the code originated from different places on the web.  I have tried to give credit where I can.

Install:  This consists of three files: classes/custom-post.php, js/slug_noedit.js, and js/tax_nodelete.js.  Simply copy these to their respective location.  That's it.

Usage:  Create your own class extending this one.  Look in the examples/ directory for ideas.  um, there is only one there right now.  more coming though...  I would be must grateful if anyone could contribute an example or two...

I shall endeavor to continue working on this readme, as well as better notes in the code.

Notes:

Capabilities:  Automatically creates unique caps, based on the slug for the CPT.  Also, adds the proper caps to the Administrator role.

<?php
// Template name: Contact Page
get_header();
// inner hero
get_template_part('template-parts/inner', 'hero');
?>
<div class="wrapper">
    <div class="contact-form">
        <?php
        $home_url = get_home_url();
        ?>
        <form action="<?php echo $home_url; ?>" method="post" id="contact-form" class="contact-form" novalidate>
            <div class="form-control">
                <label for="name">Name <span>*</span></label>
                <input type="text" name="name" id="name" required placeholder="Your Name" tabindex="1" title="Please enter your name (at least 2 characters)" class="form-control-field">
            </div>
            <div class="form-control">
                <label for="email">Email <span>*</span></label>
                <input type="email" name="email" id="email" required placeholder="Your Email" tabindex="2" title="Please enter a valid email address" class="form-control-field">
            </div>
            <div class="form-control">
                <label for="message">Message <span>*</span></label>
                <textarea name="message" id="message" cols="30" rows="10" required placeholder="Your Message" tabindex="3" title="Please enter your message (at least 10 characters)" class="form-control-field"></textarea>
            </div>
            <div class="form-control">
                <input type="submit" value="Send" class="btn btn-dblue" tabindex="4" title="Click here to submit your message">
            </div>
            <div class="response-wrapper">
                <p></p>
            </div>
        </form>
    </div>
</div>
<?php get_footer(); ?>
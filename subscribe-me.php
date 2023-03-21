<?php
/*
* Plugin Name: Subscribe Me
* Author: Kushankur Das
* Description: Posts summary on Admin Mail at EOD
* Text Domain: subscribe-me
*/

//For Trial at admin site
function my_add_menu_pages()
{
    add_menu_page(
        'Subscribe Me',
        'Subscribe Me',
        'manage_options',
        'subscribe-me',
        'subscribe_me_callback',
        'dashicons-email',
        10
    );
}
add_action('admin_menu', 'my_add_menu_pages');

function subscribe_me_callback()
{
?>
    <!--Add Input fields on Schedule Content Page-->
    <div class="wrap subs-wrap">

        <form class="subscribe-me-form" method="post">
            <input type="hidden" name="action" value="subs_form">

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" /><br />

            <input type="submit" name="submit" value="Subscribe" />

        </form>
    </div>

<?php

    if (isset($_POST['email'])) {
        $email = sanitize_email($_POST['email']);
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

        if (preg_match($pattern, $email)) {
            if (isset($_POST['submit'])) {

                $subs_emails = get_option('subs_emails');

                if (!$subs_emails) {
                    $subs_emails = array();
                }

                if (in_array($email, $subs_emails)) {
                    echo '<script>alert("You are already subscribed!");</script>';
                } else {
                    $subs_emails[] = $email;
                    update_option('subs_emails', $subs_emails);

                    // Display a success message
                    echo '<script>alert("You have been subscribed Successfully!");</script>';

                    send_subscription_mail($email);
                }
            }
        } else {
            //Display Error Message
            echo '<div class="error"><p>Invalid Email: Please Enter Valid Email Address</p></div>';
        }
    }
}

function subscribe_me_add_form()
{
    subscribe_me_callback();
}
add_action('wp_head', 'subscribe_me_add_form');

function send_subscription_mail($to)
{
    $subject = 'Congratulations! You are Subscribed';
    $summary = get_daily_post_summary();
    $message = 'You are Successfully added to our Daily Update List';
    $message .= "\n\n";
    $message .= "Here are our Top latest Posts";
    $message .= "\n";
    foreach ($summary as $post_data) {
        $message .= 'Title: ' . $post_data['title'] . "\n";
        $message .= 'URL: ' . $post_data['url'] . "\n";
        $message .= "\n";
    }

    $headers = array(
        'From: kushankur.das@wisdmlabs.com',
        'Content-Type: text/html; charset=UTF-8'
    );

    wp_mail($to, $subject, $message, $headers);
};

function get_daily_post_summary()
{
    $args = array(
        'date_query' => array(
            array(
                'after' => '24 hours ago',
            ),
        ),
    );
    $query = new WP_Query($args);
    $posts = $query->posts;
    $summary = array();

    foreach ($posts as $post) {
        $post_data = array(
            'title' => $post->post_title,
            'url' => get_permalink($post->ID),
        );
        array_push($summary, $post_data);
    }

    return $summary;
}
?>
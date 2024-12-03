<?php
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        // Retrieve form inputs
                        $name = $_POST['name'];
                        $email = $_POST['email'];
                        $subject = $_POST['subject'];
                        $message = $_POST['message'];

                        // Recipient email
                        $to = "msalmanahmad1258@gmail.com";

                        // Subject of the email
                        $email_subject = "New Contact Form Submission: " . $subject;

                        // Email body
                        $email_body = "You have received a new message from the contact form on your website.\n\n";
                        $email_body .= "Here are the details:\n";
                        $email_body .= "Name: " . $name . "\n";
                        $email_body .= "Email: " . $email . "\n";
                        $email_body .= "Subject: " . $subject . "\n";
                        $email_body .= "Message: \n" . $message . "\n";

                        // Headers
                        $headers = "From: " . $email . "\r\n";
                        $headers .= "Reply-To: " . $email . "\r\n";

                        // Send email
                        if (mail($to, $email_subject, $email_body, $headers)) {
                            echo '<div class="alert alert-success" role="alert">Message sent successfully!</div>';
                            header("Location: contact.html");
                        } else {
                            echo '<div class="alert alert-danger" role="alert">Message sending failed.</div>';
                        }
                    }
                    ?>
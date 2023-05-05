<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">payment</a></li>
            <!-- <li class="breadcrumb-item active" aria-current="page"><?= $page_title ?></li> -->
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- <h6 class="card-title"><?= $page_title ?></h6> -->
                    <html>

                    <head>
                        <title>Stripe Payment</title>
                    </head>

                    <body>
                        <script src="https://checkout.stripe.com/checkout.js"></script>
                        <button class="btn btn-primary" id="customButton" type="button">Pay $50 Now</button>
                        <script>
                        var handler = StripeCheckout.configure({
                            key: '<?php echo $stripe_publishable_key; ?>',
                            image: 'https://i.ibb.co/Y49bz6x/62a382de6209494ec2b17086-1.png',
                            locale: 'auto',
                            token: function(token) {
                                var form = document.createElement('form');
                                form.method = 'POST';
                                form.action = '<?php echo base_url('payments/charge'); ?>';

                                var hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'stripeToken';
                                hiddenInput.value = token.id;
                                form.appendChild(hiddenInput);

                                document.body.appendChild(form);
                                form.submit();
                            }
                        });

                        document.getElementById('customButton').addEventListener('click', function(e) {
                            handler.open({
                                name: 'Pay With Credit Card',
                                description: 'You have total amount of $50',
                                currency: 'USD',
                                amount: 5000,
                                billingAddress: true,
                                // shippingAddress: true,
                                allowRememberMe: true,
                            });
                            e.preventDefault();
                        });

                        window.addEventListener('popstate', function() {
                            handler.close();
                        });
                        </script>
                    </body>


                    </html>
                </div>
            </div>
        </div>
    </div>
</div>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Inquiry - Star Roofing & Construction</title>
    <style>
        body { margin: 0; font-family: 'Montserrat', sans-serif; background: #f5f7f9; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 0; display: flex; flex-direction: column; }
        .inquiry-content { padding: 32px; }
        .page-title { font-size: 2rem; font-weight: 700; color: #1a365d; margin-bottom: 24px; }

        /* Inquiry Form Styling */
        .inquiry-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(26,54,93,0.08);
            padding: 32px;
            max-width: 800px;
            margin: 0 auto;
        }

        .inquiry-form { 
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #1a365d;
        }

        .form-group input{
            width: 90%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group select{
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group textarea{
            width: 96%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1a365d;
            box-shadow: 0 0 0 2px rgba(26, 54, 93, 0.2);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .button-container {
            text-align: center;
            margin-top: 1rem;
        }

        button[type="submit"] {
            background: #1a365d;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            font-family: 'Montserrat', sans-serif;
        }

        button[type="submit"]:hover {
            background: #2c5282;
        }

        .contact-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
            border-left: 4px solid #e9b949;
        }

        .contact-info p {
            margin: 0;
            color: #4a5568;
            line-height: 1.6;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .inquiry-form {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .dashboard-container { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
            <div class="inquiry-content">
                <div class="page-title"><i class="fa fa-question-circle"></i> Make an Inquiry</div>

                <!-- Contact Information -->
                <div class="contact-info">
                    <p><strong>For immediate assistance, contact:</strong><br>
                    Ms. Janice M. Francisco - Account Supervisor<br>
                    (Smart) 0908-620-23-813 / (Sun) 0933-628-3312<br>
                    Tel. No.: (044) 329-0881</p>
                </div>

                <!-- Inquiry Form -->
                <div class="inquiry-container">
                    <form id="inquiryForm">
                        <div class="inquiry-form">
                            <div class="form-group">
                                <label for="firstname">First Name</label>
                                <input type="text" id="firstname" name="firstname" placeholder="Your First Name" required>
                            </div>
                            <div class="form-group">
                                <label for="lastname">Last Name</label>
                                <input type="text" id="lastname" name="lastname" placeholder="Your Last Name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="Your Phone Number" required>
                            </div>
                            <div class="form-group full-width">
                                <label for="inquiry_type">Type of Inquiry</label>
                                <select id="inquiry_type" name="inquiry_type" required>
                                    <option value="" disabled selected>Select Inquiry Type</option>
                                    <option value="residential">Residential Construction</option>
                                    <option value="commercial">Commercial Construction</option>
                                    <option value="renovation">Renovation Services</option>
                                    <option value="consultation">Consultation</option>
                                    <option value="quotation">Quotation Request</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group full-width">
                                <label for="message">Your Inquiry</label>
                                <textarea id="message" name="message" placeholder="Please provide details about your inquiry, including project type, timeline, budget, and any specific requirements..." required></textarea>
                            </div>
                            <div class="form-group full-width button-container">
                                <button type="submit"><i class="fa fa-paper-plane"></i> SUBMIT INQUIRY</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            $(document).ready(function(){
                // Form submission with AJAX
                $("#inquiryForm").on("submit", function(e){
                    e.preventDefault();

                    // Simple form validation
                    let isValid = true;
                    $(this).find('input, select, textarea').each(function() {
                        if ($(this).prop('required') && !$(this).val()) {
                            isValid = false;
                            $(this).addClass('error');
                        } else {
                            $(this).removeClass('error');
                        }
                    });

                    if (!isValid) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing Information',
                            text: 'Please fill in all required fields.',
                            confirmButtonColor: '#1a365d'
                        });
                        return;
                    }

                    // Show loading state
                    const submitBtn = $(this).find('button[type="submit"]');
                    const originalText = submitBtn.html();
                    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

                    // AJAX submission
                    $.ajax({
                        url: "save_inquiry.php",
                        type: "POST",
                        data: $(this).serialize(),
                        success: function(response){
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Inquiry Submitted!',
                                text: 'Thank you for your inquiry. We will get back to you within 24 hours.',
                                confirmButtonColor: '#1a365d'
                            }).then(() => {
                                // Reset form
                                $("#inquiryForm")[0].reset();
                            });
                            
                            submitBtn.html(originalText).prop('disabled', false);
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Submission Failed',
                                text: 'There was a problem submitting your inquiry. Please try again.',
                                confirmButtonColor: '#1a365d'
                            });
                            
                            submitBtn.html(originalText).prop('disabled', false);
                        }
                    });
                });
            });

            success: function(response){
            try {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Inquiry Submitted!',
                        text: 'Thank you for your inquiry. We will get back to you within 24 hours.',
                        confirmButtonColor: '#1a365d'
                    }).then(() => {
                        $("#inquiryForm")[0].reset();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Something went wrong.',
                        confirmButtonColor: '#1a365d'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Unexpected Response',
                    text: 'Please try again later.',
                    confirmButtonColor: '#1a365d'
                });
            }

            submitBtn.html(originalText).prop('disabled', false);
        },
            </script>
        </div>
    </div>
</body>
</html>
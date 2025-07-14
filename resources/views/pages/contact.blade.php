@extends('layouts.master')
@section('title','Kapcsolat')
@section('content')
<main class="contact-page bg-light">
    
    <section class="contact">
        <div class="container-fluid">
            <div class="row justify-content-center align-items-center page-header-title">
                <div class="col-md-6 text-center mb-5">
                    <h2 class="heading-section section-header ">Kapcsolat</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="dbox w-100 text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            
                            <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-geo-alt"></i></div>
                        </div>
                        <div class="text">
                        <p><span>Telephely:</span><br> 2600 Vác,<br> Zrínyi Miklós utca 42/b</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dbox w-100 text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            
                            <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-envelope"></i></div>
                        </div>
                        <div class="text">
                        <p><span>Email:</span><br> info@electrobusiness.hu<br> electrobusiness2000@gmail.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dbox w-100 text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            
                            <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-telephone"></i></div>
                        </div>
                        <div class="text">
                        <p><span>Telefon:</span><br> +36 20 292 3769 <br>+36 30 350 8644</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dbox w-100 text-center">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            
                            <div class="feature bg-primary bg-gradient text-white rounded-3 mb-3"><i class="bi bi-clock"></i></div>
                        </div>
                        <div class="text">
                        <p><span>Nyitvatartás:</span><br> H-P: 7:00-17:00</p>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="row justify-content-center align-items-center py-5">
                <div class="col-md-12">
                    <div class="wrapper bg-dark contact-wrapper">
                        <div class="row no-gutters mb-5">
                            <div class="col-md-7">
                                <div class="contact-leftside-wrap w-100 p-md-5 p-4">
                                        <h3 class="mb-4 text-light">írj nekünk üzenetet</h3>
                                        
                                        <form method="POST" action="{{route('sendContact')}}" id="contactform" name="contactform" class="contactform">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="label text-light" for="name">Teljes Név</label>
                                                        <input type="text" class="form-control" name="name" id="name" placeholder="Példa János">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="label text-light" for="email">Email</label>
                                                        <input type="email" class="form-control" name="email" id="email" placeholder="pelda@email.hu">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="label text-light" for="subject">Tárgy</label>
                                                        <input type="text" class="form-control" name="subject" id="subject" placeholder="Téma">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="label text-light" for="message">Üzenet</label>
                                                        <textarea name="message" class="form-control" id="message" cols="30" rows="4" placeholder="Üzenet"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <input type="submit" value="Küldés" class="btn btn-primary bg-gradient">
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                </div>
                            </div>
                            <div class="col-md-5 d-flex align-items-stretch">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d10721.367335297784!2d19.11973862033584!3d47.79420903931936!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47402b74aa28dfef%3A0x32842015793a388c!2sElectro%20Business%20Kft.!5e0!3m2!1shu!2shu!4v1723138038179!5m2!1shu!2shu" width="600" height="auto" style="border:0;overflow:hidden;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </iframe>
                            </div>
                            </div>
                    </div>
                </div>
                
            </div>
            
        </div>
    </section>
    
</main>
@endsection
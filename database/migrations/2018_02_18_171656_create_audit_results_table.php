<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('web_domain');
            $table->string('host_ip');
            $table->text('asn_info');
            $table->text('ssl_info');
            $table->boolean('ssl_expired')->default(false);
            $table->boolean('ssl_heartbleed')->default(false);
            $table->text('dns_info');
            $table->text('cname_info');
            $table->text('txt_info');
            $table->text('whois_info');
            $table->boolean('whois_registrant')->default(false);
            $table->string('whois_domain_owner')->default('');
            $table->string('whois_domain_email')->default('');
            $table->text('openresolver_info');
            $table->boolean('openresolver_vuln')->default(false);
            $table->text('mx_info');
            $table->text('smtp_info');
            $table->boolean('smtp_openrelay')->default(false);
            $table->text('dmarc_info');
            $table->boolean('dmarc_needed')->default(false);
            $table->text('spf_info');
            $table->boolean('spf_needed')->default(false);
            $table->integer('saverity_info')->default(0);
            $table->integer('saverity_low')->default(0);
            $table->integer('saverity_medium')->default(0);
            $table->integer('saverity_high')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_results');
    }
}

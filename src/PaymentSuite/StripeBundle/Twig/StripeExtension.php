<?php

/*
 * This file is part of the PaymentSuite package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

namespace PaymentSuite\StripeBundle\Twig;

use Symfony\Component\Form\FormFactory;
use Twig_Extension;
use Twig_SimpleFunction;

use PaymentSuite\PaymentCoreBundle\Services\Interfaces\PaymentBridgeInterface;
use PaymentSuite\StripeBundle\Router\StripeRoutesLoader;

/**
 * Text utilities extension
 *
 */
class StripeExtension extends Twig_Extension
{
    /**
     * @var FormFactory
     *
     * Form factory
     */
    protected $formFactory;

    /**
     * @var \Twig_Environment
     *
     * Twig environment
     */
    private $environment;

    /**
     * @var string
     *
     * Public key
     */
    private $publicKey;

    /**
     * @var PaymentBridgeInterface
     *
     * Currency wrapper
     */
    private $paymentBridgeInterface;

    /**
     * @var string
     *
     * View template name in Bundle notation
     */
    protected $viewTemplate;

    /**
     * @var string
     *
     * Scripts template in Bundle notation
     */
    protected $scriptsTemplate;

    /**
     * Construct method
     *
     * @param string                 $publicKey              Public key
     * @param FormFactory            $formFactory            Form factory
     * @param PaymentBridgeInterface $paymentBridgeInterface Payment Bridge Interface
     * @param string                 $viewTemplate           Twig template name for displaying the form
     * @param string                 $scriptsTemplate        Twig template name for scripts/js
     */
    public function __construct(
        $publicKey,
        FormFactory $formFactory,
        PaymentBridgeInterface $paymentBridgeInterface,
        $viewTemplate,
        $scriptsTemplate
    ) {
        $this->publicKey = $publicKey;
        $this->formFactory = $formFactory;
        $this->paymentBridgeInterface = $paymentBridgeInterface;
        $this->viewTemplate = $viewTemplate;
        $this->scriptsTemplate = $scriptsTemplate;
    }

    /**
     * Init runtime
     *
     * @param \Twig_Environment $environment Twig environment
     *
     * @return StripeExtension self object
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Return all filters
     *
     * @return array Filters created
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('stripe_render', array($this, 'renderPaymentView')),
            new Twig_SimpleFunction('stripe_scripts', array($this, 'renderPaymentScripts'))
        );
    }

    /**
     * Render stripe form view
     *
     * @param string $viewTemplate An optional template to render.
     *
     * @return string view html
     */
    public function renderPaymentView($viewTemplate = null)
    {
        $formType = $this->formFactory->create('stripe_view');

        return $this->environment->display($viewTemplate ?: $this->viewTemplate, array(
            'stripe_form'  =>  $formType->createView(),
            'stripe_execute_route' =>  StripeRoutesLoader::ROUTE_NAME,
        ));
    }

    /**
     * Render stripe scripts view
     *
     * @return string js code needed by Stripe behaviour
     */
    public function renderPaymentScripts()
    {
        return $this->environment->display($this->scriptsTemplate, array(
            'public_key'    =>  $this->publicKey,
            'currency'      =>  $this->paymentBridgeInterface->getCurrency(),
        ));
    }

    /**
     * return extension name
     *
     * @return string extension name
     */
    public function getName()
    {
        return 'payment_stripe_extension';
    }
}

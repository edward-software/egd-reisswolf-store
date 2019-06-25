<?php

namespace Paprec\PublicBundle\Controller;

use GuzzleHttp\Client;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommonController extends Controller
{


    /**
     * Get menu from WordPress API
     * @param $slug
     * @param bool $isMobile
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function getMenuFromWPAction($slug, $isMobile = false)
    {
        try {

            $client = new Client(['base_uri' => $this->getParameter('paprec_public_site_url')]);
            if ($slug == 'header-menu') {
                $response = $client->request('GET', '/wp-json/menus/v2/header');
            } else {
                $response = $client->request('GET', '/wp-json/menus/v1/menus/' . $slug);
            }

            $bodyResponse = json_decode($response->getBody(), true);

            if ($slug == 'shortlinks-menu') {

                $shortlinks = array();
                if (isset($bodyResponse['items']) && is_array($bodyResponse['items']) && count($bodyResponse['items'])) {
                    foreach ($bodyResponse['items'] as $item) {
                        $shortlinks[] = array(
                            'id' => $item['ID'],
                            'title' => $item['title'],
                            'url' => $item['url']
                        );
                    }
                }

                if ($isMobile) {
                    return $this->render('@PaprecPublic/Menu/mobile/shortlinksMenu.html.twig', array(
                        'items' => $shortlinks
                    ));
                } else {
                    return $this->render('@PaprecPublic/Menu/shortlinksMenu.html.twig', array(
                        'items' => $shortlinks
                    ));
                }

            } elseif ($slug == 'header-menu') {

                $headers = $bodyResponse;

                /*if (isset($bodyResponse['items']) && is_array($bodyResponse['items']) && count($bodyResponse['items'])) {
                    foreach ($bodyResponse['items'] as $item) {
                        $headers[$item['ID']] = array(
                            'id' => $item['ID'],
                            'title' => $item['title'],
                            'url' => $item['url'],
                            'submenus' => array()
                        );
                        if ($item['child_items'] != null && count($item['child_items'])) {
                            foreach ($item['child_items'] as $childItem) {
                                $headers[$item['ID']]['submenus'][$childItem['ID']] = array(
                                    'id' => $childItem['ID'],
                                    'title' => $childItem['title'],
                                    'url' => $childItem['url'],
                                    'submenus' => array()
                                );
//                                if ($childItem['child_items'] !== null && count($childItem['child_items'])) {
//                                    foreach ($childItem['child_items'] as $greatChildItem) {
//                                        $headers[$item['ID']]['submenus'][$childItem['ID']]['submenus'][$greatChildItem['ID']] = array(
//                                            'id' => $greatChildItem['ID'],
//                                            'title' => $greatChildItem['title'],
//                                            'url' => $greatChildItem['url']
//                                        );
//                                    }
//
//                                }
                            }
                        }
                    }
                }*/

                if ($isMobile) {
                    return $this->render('@PaprecPublic/Menu/mobile/headersMenu.html.twig', array(
                        'items' => $headers
                    ));
                } else {
                    return $this->render('@PaprecPublic/Menu/headersMenu.html.twig', array(
                        'items' => $headers
                    ));
                }
            } elseif ($slug == 'footer-menu') {
                $footers = array();
                if (isset($bodyResponse['items']) && is_array($bodyResponse['items']) && count($bodyResponse['items'])) {
                    foreach ($bodyResponse['items'] as $item) {
                        if ($item['menu_item_parent'] == '0') {
                            $footers[$item['ID']] = array(
                                'id' => $item['ID'],
                                'title' => $item['title'],
                                'url' => $item['url'],
                                'submenus' => array()
                            );
                            if ($item['child_items'] != null && count($item['child_items'])) {
                                foreach ($item['child_items'] as $childItem) {
                                    $footers[$item['ID']]['submenus'][$childItem['ID']] = array(
                                        'id' => $childItem['ID'],
                                        'title' => $childItem['title'],
                                        'url' => $childItem['url']
                                    );
                                }

                            }
                        }
                    }
                }

                return $this->render('@PaprecPublic/Menu/footersMenu.html.twig', array(
                    'items' => $footers
                ));
            } elseif ($slug == 'quicklinksmenu-footer') {

                $quicklinksmenus = array();
                if (isset($bodyResponse['items']) && is_array($bodyResponse['items']) && count($bodyResponse['items'])) {
                    foreach ($bodyResponse['items'] as $item) {
                        $quicklinksmenus[] = array(
                            'id' => $item['ID'],
                            'title' => $item['title'],
                            'url' => $item['url']
                        );
                    }
                }

                return $this->render('@PaprecPublic/Menu/quicklinksMenu.html.twig', array(
                    'items' => $quicklinksmenus
                ));
            }

        } catch
        (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new Exception('cannotLoadMenuWorpress', 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $label
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCTAsBottomAction($label, $cartUuid, $division, $stepBack = '', $nextStep = '', $idSubmit = '', $cartEmpty = false)
    {

        return $this->render('@PaprecPublic/Common/partial/ctaBottomPartial.html.twig', array(
            'cartUuid' => $cartUuid,
            'label' => $label,
            'stepBack' => $stepBack,
            'nextStep' => $nextStep,
            'division' => $division,
            'idSubmit' => $idSubmit,
            'cartEmpty' => $cartEmpty
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getSideBarInfoAction()
    {
        return $this->render('@PaprecPublic/Common/partial/sidebarInfoPartial.html.twig');
    }


    /**
     * Retourne le twig.html du cart avec les produits dans celui-ci ainsi que le montant total
     *
     * @Route("/common/loadPopupRecycle", name="paprec_public_common_loadPopupRecycle", condition="request.isXmlHttpRequest()")
     * @throws \Exception
     */
    public function loadPopupRecycle(Request $request)
    {
        return $this->render('@PaprecPublic/Common/partial/recyclePopup.html.twig');
    }
}


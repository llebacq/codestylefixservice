<?php

namespace TestCMS\Controllers;

use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Html\Renderer\Twig\TwigTemplate;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\Utils\WebLibraryManager\WebLibrary;
use Mouf\Html\Widgets\EvoluGrid\EvoluGrid;
use Mouf\Html\Widgets\EvoluGrid\EvoluGridResultSet;
use Mouf\Html\Widgets\EvoluGrid\SimpleColumn;
use Mouf\Html\Widgets\EvoluGrid\TwigColumn;
use Mouf\Html\Widgets\MessageService\Service\UserMessageInterface;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Mvc\Splash\HtmlResponse;
use TestCMS\Model\Bean\FooBean;
use TestCMS\Model\Dao\Generated\DaoFactory;
use Twig_Environment;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * TODO: write controller comment.
 */
class FooController extends Controller
{
    /**
             * The template used by this controller.
             *
             * @var TemplateInterface
     */
    private $template;

    /**
     * The main content block of the page.
     *
     * @var HtmlBlock
     */
    private $content;

    /**
     * The DAO factory object.
     *
     * @var DaoFactory
     */
    private $daoFactory;

    /**
     * The Twig environment (used to render Twig templates).
     *
     * @var Twig_Environment
     */
    private $twig;

    /**
     * Controller's constructor.
     *
     * @param TemplateInterface $template   The template used by this controller
     * @param HtmlBlock         $content    The main content block of the page
     * @param DaoFactory        $daoFactory The object in charge of retrieving DAOs
     * @param Twig_Environment  $twig       The Twig environment (used to render Twig templates)
     */
    public function __construct(TemplateInterface $template, HtmlBlock $content, DaoFactory $daoFactory, Twig_Environment $twig)
    {
        $this->template   = $template;
        $this->content    = $content;
        $this->daoFactory = $daoFactory;
        $this->twig       = $twig;
    }

    /**
     * @URL foo/list
     * @Get
     *
     * @return HtmlResponse
     */
    public function displayFrontList()
    {
        $items       = $this->daoFactory->getFooDao()->findAll();
        $itemUrl     = 'foo/';
        $itemUrlEdit = 'foo/admin/edit?id=';
        $this->content->addHtmlElement(new TwigTemplate($this->twig, 'views/foo/front/list.twig',
            [
                'items'       => $items,
                'itemUrl'     => $itemUrl,
                'itemUrlEdit' => $itemUrlEdit,
            ]));

        return new HtmlResponse($this->template);
    }

    /**
             * @URL foo/admin/list
             * @Get
             *
             * @return HtmlResponse
     */
    public function displayBackList()
    {
        $evoluGrid = new EvoluGrid();
        $evoluGrid->setUrl('foo/admin/getAll');

        $evoluGrid->setLimit(10);
        $evoluGrid->setId('fooGrid');
        $evoluGrid->setClass('table table-striped table-hover');

        $this->content->addHtmlElement(new TwigTemplate($this->twig, 'views/foo/back/list.twig',
            [
                'items' => $evoluGrid,
            ]));

        return new HtmlResponse($this->template);
    }

    /**
             * @URL foo/admin/getAll
             * @Get
             *
             * @param int|null $limit
             * @param int|null $offset
             * @param string   $output
     *
     * @return Response
     */
    public function getAllFoo($limit = null, $offset = null, $output = 'json')
    {
        $evoluGridRs = new EvoluGridResultSet();

        $evoluGridRs->addColumn(new SimpleColumn('Title', 'title', true));
        $evoluGridRs->addColumn(new TwigColumn('Creation date', "{{ created_at | date('d-m-Y H:i:s') }}", 'created_at'));
        $evoluGridRs->addColumn(new TwigColumn('Edition',
            "<a class='btn btn-xs btn-primary' href='edit?id={{ id }}' target='_blank'><i class='glyphicon glyphicon-pencil'></i></a>" .
            "<a class='btn btn-xs btn-danger' href='delete?id={{ id }}' onclick='return(confirm(\"Are you sure you want to delete this item ?\"));'><i class='glyphicon glyphicon-trash'></i></a>"));

        $data = $this->daoFactory->getFooDao()->findAll();
        $rows = $data->take($offset, $limit);

        $evoluGridRs->setResults($rows->jsonSerialize());
        $evoluGridRs->setTotalRowsCount($data->count());
        $evoluGridRs->setFormat($output);

        return $evoluGridRs->run();
    }

    /**
             * @URL foo/admin/edit
             * @Get
             *
             * @param int|null $id
     *
     * @return HtmlResponse
     */
    public function editItem($id = null)
    {
        $item        = null;
        $itemUrl     = 'foo/';
        $itemSaveUrl = 'foo/admin/save';
            if (isset($id)) {
                $item = $this->daoFactory->getFooDao()->getById($id);
            }
            $webLibrary = new WebLibrary(
                ['//cdn.ckeditor.com/4.5.2/standard/ckeditor.js'],
                []
            );

            $this->template->getWebLibraryManager()->addLibrary($webLibrary);
            $this->content->addHtmlElement(new TwigTemplate($this->twig, 'views/foo/back/edit.twig',
                [
                    'item'        => $item,
                    'itemUrl'     => $itemUrl,
                    'itemSaveUrl' => $itemSaveUrl,
                ]));

        return new HtmlResponse($this->template);
    }

    /**
     * @URL foo/{slug}
     * @Get
     *
     * @param string $slug
     *
     * @return HtmlResponse
     */
    public function displayItem($slug)
    {
        $item = $this->daoFactory->getFooDao()->getBySlug($slug);
        $this->content->addHtmlElement(new TwigTemplate($this->twig, 'views/foo/front/item.twig', ['item' => $item]));

        return new HtmlResponse($this->template);
    }

    /**
     * @URL foo/admin/delete
     * @Get
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteItem($id)
    {
                if (isset($id)) {
                    $item = $this->daoFactory->getFooDao()->getById($id);
                    $this->daoFactory->getFooDao()->delete($item);
                    set_user_message('Item successfully deleted', UserMessageInterface::SUCCESS);
                } else {
                    set_user_message('Item id not found', UserMessageInterface::ERROR);
                }

        return new RedirectResponse(ROOT_URL . 'foo/admin/list');
    }

    /**
     * @URL foo/admin/save
     * @Post
     *
     * @param string   $title
     * @param int|null $id
     * @param string   $shortText
     * @param string   $itemContent
     *
     * @return RedirectResponse
     */
    public function save($title, $id = null, $shortText = '', $itemContent = '')
    {
        if (isset($id)) {
            $item = $this->daoFactory->getFooDao()->getById($id);
        } else {
            $item = new FooBean();
        }
        $slug = $item->slugify($title);
        $item->setTitle($title);
        $item->setSlug($slug);
        $item->setShortText($shortText);
        $item->setContent($itemContent);
        $this->daoFactory->getFooDao()->save($item);

            $uploadDir = ROOT_PATH . 'public/media/foo/' . $item->getId() . '/';
            $uploadUrl = ROOT_URL . 'public/media/foo/' . $item->getId() . '/';

        if (isset($_FILES)) {
            if (isset($_FILES['vignette']) && $_FILES['vignette']['error'] !== 4) {
                $docName = $item->saveFile($_FILES['vignette'], $uploadDir, ['jpg', 'png', 'jpeg', 'bmp']);

                $item->setImage($uploadUrl . $docName);
                $this->daoFactory->getFooDao()->save($item);
            }
        }

        set_user_message('Item successfully created !', UserMessageInterface::SUCCESS);

        return new RedirectResponse(ROOT_URL . 'foo/admin/list');
    }
}

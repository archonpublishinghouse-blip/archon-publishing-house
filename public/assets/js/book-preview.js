(()=>{
    'use strict';

    const storageKey='archon.book.visitor.v1';
    const historyKey='archonBookReader';
    const historyVersion=2;
    const historyParameter='book-page';
    const clean=value=>String(value||'').trim().replace(/\s+/g,' ');
    const readName=()=>{try{return clean(localStorage.getItem(storageKey));}catch{return '';}};
    const writeName=value=>{try{localStorage.setItem(storageKey,value);}catch{}};
    const removeName=()=>{try{localStorage.removeItem(storageKey);}catch{}};

    document.addEventListener('DOMContentLoaded',()=>{
        const book=document.querySelector('[data-preview]');
        if(!book)return;

        const stage=book.querySelector('[data-stage]');
        const sheets=[...book.querySelectorAll('[data-sheet]')];
        const sheetCount=sheets.length;
        const templates=[...book.querySelectorAll('template[data-book-page]')];
        const pageIds=templates.map(template=>template.dataset.bookPage).filter(Boolean);
        const contentPageCount=pageIds.length;
        const previous=book.querySelector('[data-prev]');
        const next=book.querySelector('[data-next]');
        const reset=book.querySelector('[data-reset]');
        const changeName=book.querySelector('[data-change]');
        const removeNameButton=book.querySelector('[data-remove]');
        const progress=book.querySelector('[data-progress]');
        const dialog=document.querySelector('[data-book-welcome]');
        const form=document.querySelector('[data-book-welcome-form]');
        const input=document.querySelector('[data-book-name-input]');
        const error=document.querySelector('[data-book-name-error]');
        const dialogTitle=document.querySelector('[data-book-welcome-title]');
        const dialogMessage=document.querySelector('[data-book-welcome-message]');
        const mobileMedia=matchMedia('(max-width: 800px)');

        if(!stage||sheets.length<10||!previous||!next||!reset||!progress)return;

        const animationStyle=document.createElement('style');
        animationStyle.textContent=`
            .preview-turn-layer{position:absolute;inset:0;z-index:200;overflow:hidden;pointer-events:none;perspective:2500px}
            .preview-turn-overlay,.preview-turn-underlay{position:absolute;top:0;width:50%;height:100%;overflow:hidden;padding:clamp(1.7rem,3.1vw,4rem);background:#f2ebd0;background-image:radial-gradient(rgba(91,69,28,.13) .5px,transparent .7px);background-size:7px 7px;color:#0a2223}
            .preview-turn-overlay{z-index:2;backface-visibility:hidden;-webkit-backface-visibility:hidden;transform-style:preserve-3d}
            .preview-turn-underlay{z-index:1}
            .preview-turn-overlay.preview-face,.preview-turn-underlay.preview-face{inset:0 auto auto}
            .preview-turn-overlay.is-left,.preview-turn-underlay.is-left{left:0;transform-origin:right center}
            .preview-turn-overlay.is-right,.preview-turn-underlay.is-right{right:0;transform-origin:left center}
            .preview-turn-overlay.next-out{animation:previewNextOut .32s cubic-bezier(.42,0,1,1) both}
            .preview-turn-overlay.next-in{animation:previewNextIn .32s cubic-bezier(0,0,.2,1) both}
            .preview-turn-overlay.previous-out{animation:previewPreviousOut .32s cubic-bezier(.42,0,1,1) both}
            .preview-turn-overlay.previous-in{animation:previewPreviousIn .32s cubic-bezier(0,0,.2,1) both}
            .preview-settled-layer{position:absolute;inset:0;z-index:10;display:grid;grid-template-columns:1fr 1fr;pointer-events:auto}
            .preview-settled-page{position:relative;overflow:hidden;padding:clamp(1.7rem,3.1vw,4rem);background:#f2ebd0;background-image:radial-gradient(rgba(91,69,28,.13) .5px,transparent .7px);background-size:7px 7px;color:#0a2223}
            .preview-settled-layer.is-hidden,.preview-settled-page.is-blank{visibility:hidden}
            .preview-settled-page[aria-hidden="true"]{pointer-events:none}
            .preview-settled-page[aria-hidden="false"]{pointer-events:auto}
            @keyframes previewNextOut{from{transform:rotateY(0deg)}to{transform:rotateY(-89.9deg)}}
            @keyframes previewNextIn{from{transform:rotateY(89.9deg)}to{transform:rotateY(0deg)}}
            @keyframes previewPreviousOut{from{transform:rotateY(0deg)}to{transform:rotateY(89.9deg)}}
            @keyframes previewPreviousIn{from{transform:rotateY(-89.9deg)}to{transform:rotateY(0deg)}}
            @media(max-width:800px){.preview-turn-overlay,.preview-turn-underlay{left:0!important;right:auto!important;width:100%}.preview-settled-layer{display:block}.preview-settled-page.is-left{display:none}.preview-settled-page.is-right{width:100%;height:100%}}
            @media(prefers-reduced-motion:reduce){.preview-turn-overlay{animation-duration:.01ms!important}}
        `;
        document.head.append(animationStyle);

        sheets.slice(1,9).forEach(sheet=>sheet.style.display='none');

        const settledLayer=document.createElement('div');
        const settledLeft=document.createElement('article');
        const settledRight=document.createElement('article');
        settledLayer.className='preview-settled-layer';
        settledLeft.className='preview-settled-page is-left';
        settledRight.className='preview-settled-page is-right';
        settledLayer.append(settledLeft,settledRight);
        stage.prepend(settledLayer);

        const cover=book.querySelector('[data-face="cover"]');
        const insideFront=book.querySelector('[data-face="inside-front"]');
        const insideBack=book.querySelector('[data-face="inside-back"]');
        const backCover=book.querySelector('[data-face="back-cover"]');
        const colophon=book.querySelector('[data-face="page-16"]');
        const physicalFaces=[...book.querySelectorAll('.preview-face')];
        const coverTitle=document.createElement('h1');
        const coverByline=document.createElement('p');
        const coverImprint=document.createElement('p');

        cover.replaceChildren();
        coverTitle.textContent='YOUR EBOOK JOURNEY';
        coverImprint.textContent='Brought to Life by Archon Publishing House';
        cover.append(coverTitle,coverByline,coverImprint);
        insideFront.textContent='Inside Front Cover';
        insideBack.textContent='Inside Back Cover';
        backCover.textContent='ARCHON PUBLISHING HOUSE';
        colophon.textContent='Colophon';

        const mobileViews=[
            {id:'cover',kind:'face',face:'cover',physicalState:0,visualState:0,label:'Cover'},
            {id:'inside-front',kind:'face',face:'inside-front',physicalState:1,visualState:1,label:'Inside front cover'},
            ...pageIds.map((id,index)=>({
                id,
                kind:'page',
                pageNumber:index+1,
                physicalState:1,
                visualState:1,
                label:`Page ${index+1} of ${contentPageCount}`
            })),
            {id:'colophon',kind:'page',pageNumber:16,physicalState:9,visualState:9,label:'End'},
            {id:'inside-back',kind:'face',face:'inside-back',physicalState:9,visualState:9,label:'Inside back cover'},
            {id:'back-cover',kind:'face',face:'back-cover',physicalState:10,visualState:10,label:'Back cover'}
        ];
        const mobileViewIds=new Set(mobileViews.map(view=>view.id));
        const readerPath=book.dataset.readerPath||book.dataset.historyPath||location.pathname;

        let mode=mobileMedia.matches?'mobile':'desktop';
        let currentState=0;
        let targetState=0;
        let activeViewId='cover';
        let targetViewId='cover';
        let isAnimating=false;
        let activeSheet=null;
        let direction='none';
        let transitionToken=0;
        let timer=0;
        let activeAnimationCleanup=null;
        let historyAction='none';
        let pendingModeSync=false;
        let visitorName=readName();
        let ready=Boolean(visitorName);
        let dialogMode='none';
        let dialogOpener=null;
        let closingDialogProgrammatically=false;
        let touchStart=null;

        const templateForPage=pageNumber=>{
            if(pageNumber<1||pageNumber>contentPageCount)return null;
            return templates[pageNumber-1]||null;
        };
        const pageNumberForId=id=>{
            const index=pageIds.indexOf(id);
            return index<0?0:index+1;
        };
        const defaultViewForDesktopState=state=>{
            if(state===0)return 'cover';
            if(state===10)return 'back-cover';
            if(state===9)return 'colophon';
            if(state===1)return pageIds[0]||'cover';
            return pageIds[state*2-3]||'colophon';
        };
        const stateForView=(viewId,readerMode=mode)=>{
            if(readerMode==='mobile'){
                const index=mobileViews.findIndex(view=>view.id===viewId);
                return index<0?0:index;
            }
            if(viewId==='cover')return 0;
            if(viewId==='inside-front')return 1;
            if(viewId==='colophon'||viewId==='inside-back')return 9;
            if(viewId==='back-cover')return 10;
            const pageNumber=pageNumberForId(viewId);
            if(pageNumber===1)return 1;
            if(pageNumber>=2)return Math.floor(pageNumber/2)+1;
            return 0;
        };
        const defaultViewForState=(state,readerMode=mode)=>{
            if(readerMode==='mobile')return mobileViews[state]?.id||'cover';
            return defaultViewForDesktopState(state);
        };
        const mobileViewFor=(state,viewId)=>mobileViews[state]||mobileViews.find(view=>view.id===viewId)||mobileViews[0];
        const physicalStateFor=(state,viewId,readerMode=mode)=>{
            if(readerMode==='mobile')return mobileViewFor(state,viewId).physicalState;
            return state;
        };
        const visualStateFor=(state,viewId,readerMode=mode)=>{
            if(readerMode==='mobile')return mobileViewFor(state,viewId).visualState;
            return state;
        };
        const maxState=readerMode=>readerMode==='mobile'?mobileViews.length-1:10;

        const appendPage=(target,pageNumber)=>{
            target.replaceChildren();
            target.removeAttribute('data-face');
            if(pageNumber===16){
                const heading=document.createElement('h2');
                const copy=document.createElement('p');
                heading.textContent='Colophon';
                copy.textContent='A quiet endpaper for your eBook journey.';
                target.append(heading,copy);
                return;
            }
            const template=templateForPage(pageNumber);
            if(template){
                target.append(template.content.cloneNode(true));
                const folio=target.querySelector('.page-no');
                if(folio)folio.textContent=String(pageNumber);
            }
        };
        const pagesForDesktopState=state=>{
            if(state===1)return {left:0,right:1};
            if(state>=2&&state<=8)return {left:state*2-2,right:state*2-1};
            if(state===9)return {left:16,right:0};
            return {left:0,right:0};
        };
        const personalizeWithin=root=>{
            root.querySelectorAll('[data-book-display-name]').forEach(node=>{
                node.textContent=visitorName||'Future Author';
            });
        };
        const personalize=()=>{
            coverByline.textContent=visitorName?`Written by ${visitorName}`:'Created for a Future Author';
            personalizeWithin(book);
        };
        const renderSettledSpread=(state,viewId)=>{
            if(mode==='mobile'){
                const view=mobileViewFor(state,viewId);
                appendPage(settledLeft,0);
                if(view.kind==='page')appendPage(settledRight,view.pageNumber);
                else appendPage(settledRight,0);
                settledLayer.classList.toggle('is-hidden',view.kind!=='page');
                settledLeft.classList.add('is-blank');
                settledRight.classList.toggle('is-blank',view.kind!=='page');
            }else{
                const pages=pagesForDesktopState(state);
                appendPage(settledLeft,pages.left);
                appendPage(settledRight,pages.right);
                settledLayer.classList.toggle('is-hidden',state===0||state===10);
                settledLeft.classList.toggle('is-blank',pages.left===0);
                settledRight.classList.toggle('is-blank',pages.right===0);
            }
            personalize();
        };
        const updateCoverVisibility=(state,viewId)=>{
            if(mode==='mobile'){
                sheets[0].style.visibility=viewId==='cover'||viewId==='inside-front'?'visible':'hidden';
                sheets[9].style.visibility=viewId==='inside-back'||viewId==='back-cover'?'visible':'hidden';
                return;
            }
            sheets[0].style.visibility=state<=1?'visible':'hidden';
            sheets[9].style.visibility=state>=9?'visible':'hidden';
        };
        const setTransforms=physicalState=>{
            sheets.forEach((sheet,index)=>sheet.classList.toggle('is-flipped',index<physicalState));
        };
        const applyRestingStack=physicalState=>{
            sheets.forEach((sheet,index)=>{
                sheet.style.zIndex=String(index<physicalState?index+1:sheetCount-index);
            });
            if(physicalState<=1)sheets[0].style.zIndex='30';
            if(physicalState>=9)sheets[9].style.zIndex='30';
        };
        const setTransformsImmediately=physicalState=>{
            sheets.forEach(sheet=>sheet.style.transition='none');
            setTransforms(physicalState);
            void stage.offsetWidth;
            sheets.forEach(sheet=>sheet.style.transition='');
        };
        const label=(state,viewId)=>{
            if(mode==='mobile')return mobileViewFor(state,viewId).label;
            if(state===0)return 'Cover';
            if(state===10)return 'Back cover';
            if(state===9)return 'End';
            if(state===1)return `Page 1 of ${contentPageCount}`;
            const first=state*2-2;
            return `Pages ${first}\u2013${first+1} of ${contentPageCount}`;
        };

        const focusableSelector='a[href],button,input,select,textarea,[contenteditable="true"],[tabindex]';
        const setTreeInteractive=(root,interactive)=>{
            if(!root)return;
            root.setAttribute('aria-hidden',interactive?'false':'true');
            root.inert=!interactive;
            root.style.pointerEvents=interactive?'auto':'none';
            root.querySelectorAll(focusableSelector).forEach(element=>{
                if(!interactive){
                    if(!element.hasAttribute('data-preview-saved-tabindex')){
                        element.dataset.previewSavedTabindex=element.hasAttribute('tabindex')?element.getAttribute('tabindex'):'__none__';
                    }
                    element.setAttribute('tabindex','-1');
                    return;
                }
                if(!element.hasAttribute('data-preview-saved-tabindex'))return;
                const saved=element.dataset.previewSavedTabindex;
                if(saved==='__none__')element.removeAttribute('tabindex');
                else element.setAttribute('tabindex',saved);
                delete element.dataset.previewSavedTabindex;
            });
        };
        const updateAccessibility=()=>{
            physicalFaces.forEach(face=>setTreeInteractive(face,false));
            setTreeInteractive(settledLeft,false);
            setTreeInteractive(settledRight,false);
            settledLayer.setAttribute('aria-hidden','true');
            settledLayer.inert=true;

            if(!ready||isAnimating||dialogIsOpen())return;

            if(mode==='mobile'){
                const view=mobileViewFor(currentState,activeViewId);
                if(view.kind==='page'){
                    settledLayer.setAttribute('aria-hidden','false');
                    settledLayer.inert=false;
                    setTreeInteractive(settledRight,true);
                }else{
                    const face=book.querySelector(`[data-face="${view.face}"]`);
                    setTreeInteractive(face,true);
                }
                return;
            }

            if(currentState===0)setTreeInteractive(cover,true);
            else if(currentState===10)setTreeInteractive(backCover,true);
            else{
                settledLayer.setAttribute('aria-hidden','false');
                settledLayer.inert=false;
                if(currentState===1){
                    setTreeInteractive(insideFront,true);
                    setTreeInteractive(settledRight,true);
                }else if(currentState===9){
                    setTreeInteractive(settledLeft,true);
                    setTreeInteractive(insideBack,true);
                }else{
                    setTreeInteractive(settledLeft,true);
                    setTreeInteractive(settledRight,true);
                }
            }
        };
        const dialogIsOpen=()=>Boolean(dialog&&(dialog.open||dialog.hasAttribute('open')));
        const draw=()=>{
            const interactionBlocked=!ready||isAnimating||dialogIsOpen();
            book.dataset.state=String(visualStateFor(currentState,activeViewId));
            book.dataset.readerMode=mode;
            previous.disabled=interactionBlocked||currentState===0;
            next.disabled=interactionBlocked||currentState===maxState(mode);
            reset.disabled=interactionBlocked||currentState===0;
            if(changeName)changeName.disabled=interactionBlocked;
            if(removeNameButton)removeNameButton.disabled=interactionBlocked;
            progress.textContent=label(currentState,activeViewId);
            stage.setAttribute('aria-busy',isAnimating?'true':'false');
            settledLayer.querySelectorAll('a[data-book-page]').forEach(link=>{
                if(link.dataset.bookPage===activeViewId)link.setAttribute('aria-current','page');
                else link.removeAttribute('aria-current');
            });
            book.inert=!ready||dialogIsOpen();
            updateAccessibility();
        };

        const validViewId=viewId=>mobileViewIds.has(viewId);
        const historyView=()=>{
            const stateView=history.state?.[historyKey];
            if(stateView?.version===historyVersion&&validViewId(stateView.viewId))return stateView.viewId;
            const queryView=new URL(location.href).searchParams.get(historyParameter);
            return validViewId(queryView)?queryView:'cover';
        };
        const historyUrlFor=viewId=>{
            const url=new URL(location.href);
            url.pathname=readerPath;
            if(viewId==='cover')url.searchParams.delete(historyParameter);
            else url.searchParams.set(historyParameter,viewId);
            return `${url.pathname}${url.search}${url.hash}`;
        };
        const writeHistory=(viewId,action='push')=>{
            if(action==='none')return;
            const state={
                ...(history.state&&typeof history.state==='object'?history.state:{}),
                [historyKey]:{version:historyVersion,viewId,mode}
            };
            const url=historyUrlFor(viewId);
            if(action==='replace')history.replaceState(state,'',url);
            else history.pushState(state,'',url);
        };

        const clearOverlay=()=>stage.querySelector('.preview-turn-layer')?.remove();
        const clearActiveAnimation=()=>{
            clearTimeout(timer);
            timer=0;
            if(activeAnimationCleanup){
                const cleanup=activeAnimationCleanup;
                activeAnimationCleanup=null;
                cleanup();
            }
            clearOverlay();
            if(activeSheet!==null&&sheets[activeSheet])sheets[activeSheet].style.zIndex='';
        };
        const settleVisualImmediately=(state,viewId)=>{
            const physicalState=physicalStateFor(state,viewId);
            setTransformsImmediately(physicalState);
            applyRestingStack(physicalState);
            renderSettledSpread(state,viewId);
            updateCoverVisibility(state,viewId);
            book.dataset.state=String(visualStateFor(state,viewId));
            progress.textContent=label(state,viewId);
        };
        const syncMode=()=>{
            const requestedMode=mobileMedia.matches?'mobile':'desktop';
            if(requestedMode===mode)return;
            if(isAnimating){
                pendingModeSync=true;
                return;
            }
            mode=requestedMode;
            currentState=stateForView(activeViewId,mode);
            targetState=currentState;
            targetViewId=activeViewId;
            settleVisualImmediately(currentState,activeViewId);
            draw();
            writeHistory(activeViewId,'replace');
        };
        const finish=token=>{
            if(token!==transitionToken||!isAnimating)return;
            clearActiveAnimation();
            currentState=targetState;
            activeViewId=targetViewId;
            activeSheet=null;
            direction='none';
            isAnimating=false;
            draw();
            writeHistory(activeViewId,historyAction);
            historyAction='none';
            if(pendingModeSync){
                pendingModeSync=false;
                syncMode();
            }
        };
        const copyReadablePage=(pageNumber,side,className='preview-turn-overlay')=>{
            const page=document.createElement('article');
            page.className=`${className} is-${side}`;
            appendPage(page,pageNumber);
            personalizeWithin(page);
            return page;
        };
        const copyReadableView=(view,side,className='preview-turn-overlay')=>{
            if(view.kind==='page')return copyReadablePage(view.pageNumber,side,className);
            const source=book.querySelector(`[data-face="${view.face}"]`);
            const page=document.createElement('article');
            page.className=`preview-face ${className} is-${side}`;
            page.dataset.face=view.face;
            if(source)page.append(...[...source.childNodes].map(node=>node.cloneNode(true)));
            personalizeWithin(page);
            return page;
        };
        const animateOverlay=(overlay,className,token,onComplete)=>{
            let completed=false;
            const detach=()=>{
                overlay.removeEventListener('animationend',ended);
                overlay.removeEventListener('animationcancel',cancelled);
            };
            const complete=source=>{
                if(completed||token!==transitionToken)return;
                completed=true;
                clearTimeout(timer);
                timer=0;
                detach();
                if(activeAnimationCleanup===cancel)activeAnimationCleanup=null;
                onComplete(source);
            };
            const ended=event=>{if(event.target===overlay)complete('animationend');};
            const cancelled=event=>{if(event.target===overlay)complete('animationcancel');};
            const cancel=()=>{
                if(completed)return;
                completed=true;
                detach();
            };
            overlay.addEventListener('animationend',ended);
            overlay.addEventListener('animationcancel',cancelled);
            activeAnimationCleanup=cancel;
            timer=setTimeout(()=>complete('fail-safe'),500);
            requestAnimationFrame(()=>{
                if(token===transitionToken&&!completed)overlay.classList.add(className);
            });
        };
        const switchSettledSpread=(state,viewId)=>{
            const physicalState=physicalStateFor(state,viewId);
            setTransformsImmediately(physicalState);
            applyRestingStack(physicalState);
            renderSettledSpread(state,viewId);
            updateCoverVisibility(state,viewId);
            book.dataset.state=String(visualStateFor(state,viewId));
            progress.textContent=label(state,viewId);
        };
        const turnDesktopInterior=(delta,target,token)=>{
            const currentPages=pagesForDesktopState(currentState);
            const targetPages=pagesForDesktopState(target);
            const outgoingSide=delta>0?'right':'left';
            const incomingSide=delta>0?'left':'right';
            const outgoingPage=delta>0?currentPages.right:currentPages.left;
            const incomingPage=delta>0?targetPages.left:targetPages.right;
            const revealedPage=delta>0?targetPages.right:targetPages.left;
            const retainedPage=delta>0?currentPages.left:currentPages.right;
            const layer=document.createElement('div');
            layer.className='preview-turn-layer';
            layer.setAttribute('aria-hidden','true');
            layer.inert=true;
            let underlay=copyReadablePage(revealedPage,outgoingSide,'preview-turn-underlay');
            const outgoing=copyReadablePage(outgoingPage,outgoingSide);
            layer.append(underlay,outgoing);
            stage.append(layer);
            animateOverlay(outgoing,delta>0?'next-out':'previous-out',token,()=>{
                outgoing.remove();
                switchSettledSpread(target,targetViewId);
                underlay.remove();
                underlay=copyReadablePage(retainedPage,incomingSide,'preview-turn-underlay');
                const incoming=copyReadablePage(incomingPage,incomingSide);
                layer.append(underlay,incoming);
                animateOverlay(incoming,delta>0?'next-in':'previous-in',token,()=>finish(token));
            });
        };
        const turnMobileContent=(delta,target,token)=>{
            const currentView=mobileViewFor(currentState,activeViewId);
            const targetView=mobileViewFor(target,targetViewId);
            const outgoingSide=delta>0?'right':'left';
            const incomingSide=delta>0?'left':'right';
            const layer=document.createElement('div');
            layer.className='preview-turn-layer';
            layer.setAttribute('aria-hidden','true');
            layer.inert=true;
            let underlay=copyReadableView(targetView,outgoingSide,'preview-turn-underlay');
            const outgoing=copyReadableView(currentView,outgoingSide);
            layer.append(underlay,outgoing);
            stage.append(layer);
            animateOverlay(outgoing,delta>0?'next-out':'previous-out',token,()=>{
                outgoing.remove();
                switchSettledSpread(target,targetViewId);
                underlay.remove();
                underlay=copyReadableView(currentView,incomingSide,'preview-turn-underlay');
                const incoming=copyReadableView(targetView,incomingSide);
                layer.append(underlay,incoming);
                animateOverlay(incoming,delta>0?'next-in':'previous-in',token,()=>finish(token));
            });
        };
        const transitionMilliseconds=element=>{
            const style=getComputedStyle(element);
            const durations=style.transitionDuration.split(',').map(value=>value.trim());
            const delays=style.transitionDelay.split(',').map(value=>value.trim());
            const milliseconds=value=>value.endsWith('ms')?parseFloat(value)||0:(parseFloat(value)||0)*1000;
            return Math.max(...durations.map((duration,index)=>milliseconds(duration)+milliseconds(delays[index]||delays[0]||'0s')),0);
        };
        const turnPhysicalCover=(target,token,sheetIndex)=>{
            const sheet=sheets[sheetIndex];
            activeSheet=sheetIndex;
            sheet.style.zIndex=String(sheetCount+100);
            let completed=false;
            const cleanup=()=>{
                sheet.removeEventListener('transitionend',ended);
                completed=true;
            };
            const finalizeCover=()=>{
                if(completed||token!==transitionToken||!isAnimating)return;
                cleanup();
                clearTimeout(timer);
                timer=0;
                sheet.style.zIndex='';
                const physicalState=physicalStateFor(target,targetViewId);
                applyRestingStack(physicalState);
                renderSettledSpread(target,targetViewId);
                updateCoverVisibility(target,targetViewId);
                activeSheet=null;
                finish(token);
            };
            const ended=event=>{
                if(event.target===sheet&&event.propertyName==='transform')finalizeCover();
            };
            sheet.addEventListener('transitionend',ended);
            activeAnimationCleanup=cleanup;
            timer=setTimeout(finalizeCover,transitionMilliseconds(sheet)+150);

            const targetView=mode==='mobile'?mobileViewFor(target,targetViewId):null;
            const opensToInterior=mode==='desktop'?(target===1||target===9):(targetView?.id==='inside-front'||targetView?.id==='inside-back');
            if(opensToInterior){
                renderSettledSpread(target,targetViewId);
                updateCoverVisibility(target,targetViewId);
            }
            requestAnimationFrame(()=>{
                if(token!==transitionToken||completed)return;
                book.dataset.state=String(visualStateFor(target,targetViewId));
                setTransforms(physicalStateFor(target,targetViewId));
            });
        };
        const isPhysicalCoverMove=(from,to)=>{
            if(mode==='desktop')return (from===0&&to===1)||(from===1&&to===0)||(from===9&&to===10)||(from===10&&to===9);
            const fromId=mobileViews[from]?.id;
            const toId=mobileViews[to]?.id;
            return (fromId==='cover'&&toId==='inside-front')||
                (fromId==='inside-front'&&toId==='cover')||
                (fromId==='inside-back'&&toId==='back-cover')||
                (fromId==='back-cover'&&toId==='inside-back');
        };
        const physicalSheetForMove=(from,to)=>{
            if(mode==='desktop')return Math.max(from,to)<=1?0:9;
            const ids=[mobileViews[from]?.id,mobileViews[to]?.id];
            return ids.includes('cover')?0:9;
        };
        const beginMove=(target,viewId,action='push')=>{
            if(!ready||isAnimating)return;
            if(target<0||target>maxState(mode))return;
            if(target===currentState){
                activeViewId=viewId;
                targetViewId=viewId;
                draw();
                writeHistory(activeViewId,action);
                return;
            }
            targetState=target;
            targetViewId=viewId;
            direction=target>currentState?'forward':'backward';
            isAnimating=true;
            historyAction=action;
            const token=++transitionToken;
            draw();

            if(matchMedia('(prefers-reduced-motion: reduce)').matches){
                settleVisualImmediately(target,viewId);
                finish(token);
                return;
            }

            const delta=target>currentState?1:-1;
            if(isPhysicalCoverMove(currentState,target)){
                turnPhysicalCover(target,token,physicalSheetForMove(currentState,target));
            }else if(mode==='mobile'){
                turnMobileContent(delta,target,token);
            }else{
                turnDesktopInterior(delta,target,token);
            }
        };
        const move=delta=>{
            if(!ready||isAnimating)return;
            const target=currentState+delta;
            if(target<0||target>maxState(mode))return;
            beginMove(target,defaultViewForState(target,mode),'push');
        };
        const jumpToView=(viewId,action='push')=>{
            if(!validViewId(viewId)||!ready)return;
            beginMove(stateForView(viewId,mode),viewId,action);
        };
        const forceView=(viewId,action='none')=>{
            if(!validViewId(viewId))viewId='cover';
            ++transitionToken;
            clearActiveAnimation();
            isAnimating=false;
            activeSheet=null;
            direction='none';
            historyAction='none';
            activeViewId=viewId;
            targetViewId=viewId;
            currentState=stateForView(viewId,mode);
            targetState=currentState;
            settleVisualImmediately(currentState,activeViewId);
            draw();
            writeHistory(activeViewId,action);
        };

        const restoreDialogFocus=()=>{
            const target=dialogOpener;
            dialogOpener=null;
            if(target&&target.isConnected&&!target.disabled)setTimeout(()=>target.focus(),0);
        };
        const showDialog=changing=>{
            if(!dialog||!form||!input)return;
            dialogMode=changing?'change':'first';
            dialogOpener=changing?document.activeElement:null;
            input.value=changing?visitorName:'';
            if(error)error.textContent='';
            if(dialogTitle)dialogTitle.textContent=changing?'Welcome back.':'Every remarkable book begins with a name.';
            if(dialogMessage){
                dialogMessage.textContent=changing&&visitorName?`Welcome back, ${visitorName}. Shall we continue your story?`:'What name should appear on your story?';
            }
            if(dialog.showModal&&!dialog.open)dialog.showModal();
            else dialog.setAttribute('open','');
            draw();
            setTimeout(()=>input.focus(),0);
        };
        const hideDialog=(restoreFocus=false)=>{
            if(!dialog)return;
            closingDialogProgrammatically=true;
            if(dialog.open&&dialog.close)dialog.close();
            else dialog.removeAttribute('open');
            closingDialogProgrammatically=false;
            dialogMode='none';
            draw();
            if(restoreFocus)restoreDialogFocus();
            else dialogOpener=null;
        };

        form?.addEventListener('submit',event=>{
            event.preventDefault();
            if(event.submitter?.matches('[data-book-guest]')){
                visitorName='';
                removeName();
                ready=true;
                personalize();
                hideDialog();
                draw();
                return;
            }
            const value=clean(input?.value);
            if(value.length<2||value.length>60){
                if(error)error.textContent='Please enter a name between 2 and 60 characters.';
                input?.focus();
                return;
            }
            visitorName=value;
            writeName(value);
            ready=true;
            personalize();
            hideDialog();
            draw();
        });
        dialog?.addEventListener('cancel',event=>{
            event.preventDefault();
            if(dialogMode==='change'&&ready)hideDialog(true);
            else input?.focus();
        });
        dialog?.addEventListener('close',()=>{
            if(closingDialogProgrammatically)return;
            if(!ready||dialogMode==='first'){
                book.inert=true;
                setTimeout(()=>showDialog(false),0);
                return;
            }
            dialogMode='none';
            draw();
            restoreDialogFocus();
        });
        dialog?.addEventListener('keydown',event=>{
            if(event.key!=='Tab')return;
            const focusable=[...dialog.querySelectorAll(focusableSelector)].filter(element=>!element.disabled&&element.getAttribute('aria-hidden')!=='true');
            if(!focusable.length){
                event.preventDefault();
                input?.focus();
                return;
            }
            const first=focusable[0];
            const last=focusable[focusable.length-1];
            if(event.shiftKey&&document.activeElement===first){
                event.preventDefault();
                last.focus();
            }else if(!event.shiftKey&&document.activeElement===last){
                event.preventDefault();
                first.focus();
            }
        });

        previous.addEventListener('click',()=>move(-1));
        next.addEventListener('click',()=>move(1));
        reset.addEventListener('click',()=>{
            if(isAnimating||!ready)return;
            forceView('cover','push');
        });
        changeName?.addEventListener('click',()=>{
            if(!isAnimating&&ready)showDialog(true);
        });
        removeNameButton?.addEventListener('click',()=>{
            if(isAnimating)return;
            visitorName='';
            removeName();
            ready=false;
            forceView('cover','push');
            personalize();
            draw();
            showDialog(false);
        });
        book.addEventListener('click',event=>{
            const origin=event.target instanceof Element?event.target:null;
            const link=origin?.closest('a[data-book-page]');
            if(!link||!settledLayer.contains(link)||event.defaultPrevented)return;
            if(event.button>0||event.metaKey||event.ctrlKey||event.shiftKey||event.altKey)return;
            const viewId=link.dataset.bookPage;
            if(!validViewId(viewId)||isAnimating||!ready)return;
            event.preventDefault();
            jumpToView(viewId,'push');
        });
        document.addEventListener('keydown',event=>{
            if(event.defaultPrevented||dialogIsOpen())return;
            const target=event.target;
            if(target instanceof Element&&target.closest('input,textarea,select,[contenteditable="true"],[role="textbox"]'))return;
            if(event.key==='ArrowRight'){
                event.preventDefault();
                move(1);
            }
            if(event.key==='ArrowLeft'){
                event.preventDefault();
                move(-1);
            }
        });
        stage.addEventListener('touchstart',event=>{
            if(mode!=='mobile'||isAnimating||dialogIsOpen())return;
            const origin=event.target instanceof Element?event.target:null;
            if(origin?.closest('a,button,input,textarea,select,[contenteditable="true"]'))return;
            const touch=event.changedTouches[0];
            touchStart=touch?{x:touch.clientX,y:touch.clientY}:null;
        },{passive:true});
        stage.addEventListener('touchend',event=>{
            if(mode!=='mobile'||!touchStart||isAnimating||dialogIsOpen())return;
            const endX=event.changedTouches[0]?.clientX||0;
            const endY=event.changedTouches[0]?.clientY||0;
            const distanceX=endX-touchStart.x;
            const distanceY=endY-touchStart.y;
            touchStart=null;
            if(Math.abs(distanceX)<48||Math.abs(distanceX)<=Math.abs(distanceY))return;
            move(distanceX<0?1:-1);
        },{passive:true});
        stage.addEventListener('touchcancel',()=>{touchStart=null;},{passive:true});
        window.addEventListener('popstate',()=>{
            forceView(historyView(),'none');
        });
        if(typeof mobileMedia.addEventListener==='function')mobileMedia.addEventListener('change',syncMode);
        else if(typeof mobileMedia.addListener==='function')mobileMedia.addListener(syncMode);

        activeViewId=historyView();
        targetViewId=activeViewId;
        currentState=stateForView(activeViewId,mode);
        targetState=currentState;
        personalize();
        settleVisualImmediately(currentState,activeViewId);
        draw();
        writeHistory(activeViewId,'replace');
        if(!ready)showDialog(false);
    });
})();
